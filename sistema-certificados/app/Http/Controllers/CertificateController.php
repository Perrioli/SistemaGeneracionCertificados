<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CertificateTemplateExport;
use App\Imports\CertificatesImport;
use Illuminate\Support\Facades\File;
use iio\libmergepdf\Merger;
use Throwable;


class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Certificate::query();

        // Aplicar filtros de búsqueda si existen
        if ($request->filled('search_dni')) {
            $query->whereHas('person', function ($q) use ($request) {
                $q->where('dni', 'like', '%' . $request->search_dni . '%');
            });
        }
        if ($request->filled('search_course')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search_course . '%');
            });
        }
        // ... (puedes añadir más filtros para otras columnas)

        $certificates = $query->with(['person', 'course'])->latest()->paginate(10);
        return view('certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('nombre')->get();
        $people = Person::orderBy('apellido')->get();
        return view('certificates.create', compact('courses', 'people'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'person_id' => 'required|exists:persons,id',
            'condition' => 'required|string',
            'nota' => 'nullable|numeric|min:0',
            'unidad_academica' => 'required|string|max:255',
            'subarea' => 'required|string|max:255',
            'iniciales' => 'required|string|max:255',
        ]);

        $course = \App\Models\Course::with(['resolution', 'area'])->find($request->course_id);
        $person = \App\Models\Person::find($request->person_id);

        // Generar el CUV/unique_code (misma lógica que en el formulario)
        $areaCode = strtoupper(substr($course->area->nombre ?? '', 0, 3));
        $codigoIncremental = $person->id;
        $anio = date('Y');
        $tresUltimosDni = substr($person->dni, -3);
        $conditionMap = ['Aprobado' => 'APR', 'Asistente' => 'ASI', 'Capacitador' => 'CAP'];
        $conditionCode = $conditionMap[$request->condition] ?? '';
        $uniqueCode = $request->unidad_academica . $areaCode . $request->subarea . $codigoIncremental . $anio . $conditionCode . $request->iniciales . $tresUltimosDni;

        if (\App\Models\Certificate::where('unique_code', $uniqueCode)->exists()) {
            return back()->withInput()->withErrors(['cuv' => 'El CUV generado para este certificado ya existe. Verifique los datos.']);
        }

        // Preparar datos para las plantillas
        $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
        $data = [
            'person' => $person,
            'course' => $course,
            'certificateData' => $request->all() + ['cuv' => $uniqueCode, 'tipo_de_certificado' => $request->condition, 'horas' => $course->horas, 'ano' => $anio],
            'qr_path' => storage_path('app/public/' . $qrPath),
        ];

        // --- LÓGICA DE GENERACIÓN Y UNIÓN DE PDF OPTIMIZADA ---
        $verificationUrl = route('certificates.verify', $uniqueCode);
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qrcodes');
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

        $tempPath = storage_path('app/temp_pdf');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($tempPath);

        $pdfFront = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template_front', $data)->setPaper('a4', 'landscape');
        $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
        $pdfFront->save($frontFilePath);

        $pdfBack = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template_back', $data)->setPaper('a4', 'landscape');
        $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
        $pdfBack->save($backFilePath);

        unset($pdfFront, $pdfBack);

        $merger = new \iio\libmergepdf\Merger;
        $merger->addFile($frontFilePath);
        $merger->addFile($backFilePath);
        $finalPdfContent = $merger->merge();

        $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->put($pdfPath, $finalPdfContent);

        \Illuminate\Support\Facades\File::delete($frontFilePath, $backFilePath);
        unset($finalPdfContent);

        // Crear el registro del Certificado
        \App\Models\Certificate::create([
            'course_id'       => $course->id,
            'person_id'       => $person->id,
            'condition'       => $request->condition,
            'nota'            => $request->nota,
            'unidad_academica'  => $request->unidad_academica,
            'area_excel'        => $course->area->nombre ?? null,
            'subarea'           => $request->subarea,
            'codigo_incremental' => $codigoIncremental,
            'anio'              => $anio,
            'tipo_certificado'  => $conditionCode,
            'iniciales'         => $request->iniciales,
            'tres_ultimos_digitos_dni' => $tresUltimosDni,
            'unique_code'     => $uniqueCode,
            'qr_path'         => $qrPath,
            'pdf_path'        => $pdfPath,
        ]);

        return redirect()->route('certificates.index')->with('success', 'Certificado generado exitosamente.');
    }
    /**
     * Display the specified resource.
     */
    public function showImportForm()
    {
        return view('certificates.import');
    }


    public function import(Request $request)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls']);

        $import = new \App\Imports\CertificatesImport;

        try {
            // Ejecutamos la importación completa en un solo paso
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));
        } catch (Throwable $e) {
            // En caso de un error crítico, lo mostramos
            return redirect()->route('certificates.index')
                ->with('import_errors', ['Hubo un error crítico durante la importación: ' . $e->getMessage()]);
        }

        // Obtenemos los resultados directamente
        $importedCount = $import->getImportedCount();
        $errors = $import->getErrors();

        $successMsg = "Proceso finalizado. Se importaron " . $importedCount . " certificados exitosamente.";

        // Si hubo errores de validación en alguna fila, los mostramos
        if (!empty($errors)) {
            return redirect()->route('certificates.index')
                ->with('success', $successMsg)
                ->with('import_errors', $errors);
        }

        // Si todo fue perfecto, solo mostramos el mensaje de éxito
        return redirect()->route('certificates.index')->with('success', 'La importación se ha realizado exitosamente.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate)
    {
        // Cargamos todos los cursos y personas para los selectores del formulario
        $courses = Course::orderBy('nombre')->get();
        $people = Person::orderBy('apellido')->get();
        return view('certificates.edit', compact('certificate', 'courses', 'people'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        // 1. Validar los datos del formulario
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'person_id' => 'required|exists:persons,id',
            'condition' => 'required|string',
            'nota' => 'nullable|numeric|min:0',
        ]);

        // 2. Eliminar los archivos PDF y QR antiguos para evitar basura
        if ($certificate->pdf_path) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }
        if ($certificate->qr_path) {
            Storage::disk('public')->delete($certificate->qr_path);
        }

        // 3. Regenerar el código QR (la URL no cambia porque el código único es el mismo)
        $verificationUrl = route('certificates.verify', $certificate->unique_code);
        $qrPath = 'qrcodes/' . $certificate->unique_code . '.svg';
        QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

        // 4. Obtener los nuevos datos para el PDF
        $person = Person::find($request->person_id);
        $course = Course::find($request->course_id);
        $data = [
            'person' => $person,
            'course' => $course,
            'condition' => $request->condition,
            'nota' => $request->nota,
            'unique_code' => $certificate->unique_code,
            'qr_path' => storage_path('app/public/' . $qrPath),
            'emission_date' => $certificate->created_at->format('d/m/Y'), // Usamos la fecha original
        ];

        // 5. Regenerar el PDF del certificado con los nuevos datos
        $pdf = Pdf::loadView('certificates.pdf_template', $data)->setPaper('a4', 'landscape');
        $pdfPath = 'certificates/' . $certificate->unique_code . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // 6. Actualizar el registro en la base de datos
        $certificate->update([
            'course_id' => $request->course_id,
            'person_id' => $request->person_id,
            'condition' => $request->condition,
            'nota' => $request->nota,
            'qr_path' => $qrPath,
            'pdf_path' => $pdfPath,
        ]);

        return redirect()->route('certificates.index')
            ->with('success', 'Certificado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        if ($certificate->pdf_path) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }
        if ($certificate->qr_path) {
            Storage::disk('public')->delete($certificate->qr_path);
        }

        $certificate->delete();

        return redirect()->route('certificates.index')
            ->with('success', 'Certificado eliminado exitosamente.');
    }

    public function verify($unique_code)
    {
        $certificate = Certificate::where('unique_code', $unique_code)->firstOrFail();
        return view('certificates.verify', compact('certificate'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new CertificateTemplateExport, 'plantilla_certificados.xlsx');
    }

    public function showPreview()
    {
        $importData = session('import_data');
        if (empty($importData)) {
            return redirect()->route('certificates.import.form');
        }
        return view('certificates.preview', ['importData' => $importData]);
    }


    public function processImport()
    {
        $importData = session('import_data');
        if (empty($importData)) {
            return redirect()->route('certificates.import.form')->with('import_errors', ['No hay datos para importar o la sesión ha expirado.']);
        }

        $certificatesCreated = 0;

        foreach ($importData as $row) {
            $course = Course::with(['resolution', 'area'])->where('nombre', $row['curso'])->first();
            $person = Person::firstOrCreate(
                ['dni' => $row['dni']],
                [
                    'apellido' => $row['apellido'],
                    'nombre' => $row['nombre'],
                    'titulo' => 'N/A',
                    'domicilio' => 'N/A',
                    'telefono' => 'N/A',
                    'email' => $row['dni'] . '@email-temporal.com',
                ]
            );

            if (!$course) continue;

            $uniqueCode = $row['cuv'];

            $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
            $verificationUrl = route('certificates.verify', $uniqueCode);
            Storage::disk('public')->makeDirectory('qrcodes');
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

            $data = [
                'person' => $person,
                'course' => $course,
                'certificateData' => $row,
                'qr_path' => storage_path('app/public/' . $qrPath),
            ];

            $tempPath = storage_path('app/temp_pdf');
            File::ensureDirectoryExists($tempPath);

            $pdfFront = Pdf::loadView('certificates.pdf_template_front', $data)->setPaper('a4', 'landscape');
            $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
            $pdfFront->save($frontFilePath);

            $pdfBack = Pdf::loadView('certificates.pdf_template_back', $data)->setPaper('a4', 'landscape');
            $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
            $pdfBack->save($backFilePath);

            // **OPTIMIZACIÓN**: Liberar memoria de los objetos PDF
            unset($pdfFront, $pdfBack);

            $merger = new Merger;
            $merger->addFile($frontFilePath);
            $merger->addFile($backFilePath);
            $finalPdfContent = $merger->merge();

            $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
            Storage::disk('public')->put($pdfPath, $finalPdfContent);

            File::delete($frontFilePath, $backFilePath);

            // **OPTIMIZACIÓN**: Liberar memoria del contenido del PDF final
            unset($finalPdfContent);

            Certificate::create([
                'course_id'       => $course->id,
                'person_id'       => $person->id,
                'condition'       => $row['tipo_de_certificado'] ?? 'Aprobado',
                'nota'            => $row['nota'] ?? null,
                'unique_code'     => $uniqueCode,
                'qr_path'         => $qrPath,
                'pdf_path'        => $pdfPath,
                'unidad_academica'  => $row['unidad_academica'] ?? null,
                'area_excel'        => $row['area'] ?? null,
                'subarea'           => $row['subarea'] ?? null,
                'codigo_incremental' => $row['codigo_incremental'] ?? null,
                'anio'              => $row['ano'] ?? null,
                'tipo_certificado'  => $row['tipo_certificado'] ?? null,
                'iniciales'         => $row['iniciales'] ?? null,
                'tres_ultimos_digitos_dni' => $row['3_ultimos_del_dni'] ?? null,
            ]);

            $certificatesCreated++;
        }

        session()->forget('import_data');

        return redirect()->route('certificates.index')
            ->with('success', "Se importaron y generaron " . $certificatesCreated . " certificados exitosamente.");
    }

    public function previewPdf()
    {
        // 1. Recuperar los datos de la sesión
        $importData = session('import_data');

        // 2. Si no hay datos o están vacíos, redirigir
        if (empty($importData)) {
            return redirect()->route('certificates.import.form')->with('import_errors', ['No hay datos para previsualizar.']);
        }

        // 3. Tomar solo la primera fila de datos para la muestra
        $firstRow = $importData[0];

        // 4. Reutilizar la lógica de búsqueda de modelos
        $course = Course::with('resolution')->where('nombre', $firstRow['curso'])->first();
        $person = Person::firstOrCreate(
            ['dni' => $firstRow['dni']],
            [
                'apellido' => $firstRow['apellido'],
                'nombre'   => $firstRow['nombre'],
                'titulo'   => 'N/A',
                'domicilio' => 'N/A',
                'telefono'  => 'N/A',
                'email'    => $firstRow['dni'] . '@email-temporal.com',
            ]
        );

        if (!$course || !$person) {
            return redirect()->route('certificates.import.preview')->with('import_errors', ['No se pudieron encontrar los datos para generar la vista previa.']);
        }

        // 5. Preparar los datos para la plantilla PDF
        $data = [
            'person' => $person,
            'course' => $course,
            'certificateData' => $firstRow,
            // Para la vista previa no necesitamos un QR real, podemos pasar una ruta de imagen de ejemplo
            'qr_path' => public_path('images/logo.png'), // Usamos el logo como placeholder
        ];

        // 6. Generar el PDF y mostrarlo en el navegador sin guardarlo
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template', $data)->setPaper('a4', 'landscape');

        return $pdf->stream('previsualizacion_certificado.pdf');
    }

    public function getAreaByCourse(\App\Models\Course $course)
    {
        // Cargamos la relación 'area' y devolvemos una respuesta JSON
        $course->load('area');
        return response()->json([
            'area_name' => $course->area->nombre ?? 'Sin Área Definida'
        ]);
    }
}
