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
use Throwable;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Certificate::query();

        if ($user->role && $user->role->name === 'Persona') {

            $dni = $user->person->dni ?? null;

            if ($dni && !str_starts_with($dni, 'PENDIENTE-')) {

                $personIds = \App\Models\Person::where('dni', $dni)->pluck('id');

                $query->whereIn('person_id', $personIds);
            } else {

                $query->whereRaw('1 = 0');
            }
        }

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
        // Validar los datos
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'person_id' => 'required|exists:persons,id',
            'condition' => 'required|string',
            'nota' => 'nullable|numeric|min:0',
        ]);

        // Obtener los modelos
        $course = \App\Models\Course::with(['resolution', 'area'])->find($request->course_id);
        $person = \App\Models\Person::find($request->person_id);

        // Generar un código único si es que se genera de forma individual y sin plantilla excel
        $uniqueCode = 'CERT-' . strtoupper(uniqid());

        // Generar QR
        $verificationUrl = route('certificates.verify', $uniqueCode);
        $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('qrcodes');
        \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

        // Preparar datos para las plantillas
        $data = [
            'person' => $person,
            'course' => $course,
            'certificateData' => $request->all() + ['cuv' => $uniqueCode, 'tipo_de_certificado' => $request->condition, 'horas' => $course->horas],
            'qr_path' => storage_path('app/public/' . $qrPath),
        ];

        // LÓGICA DE UNIÓN DE PDF 
        $tempPath = storage_path('app/temp_pdf');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($tempPath);

        // Generar frente
        $pdfFront = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template_front', $data)->setPaper('a4', 'landscape');
        $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
        $pdfFront->save($frontFilePath);

        // Generar dorso
        $pdfBack = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template_back', $data)->setPaper('a4', 'landscape');
        $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
        $pdfBack->save($backFilePath);

        // Unir
        $merger = new \iio\libmergepdf\Merger;
        $merger->addFile($frontFilePath);
        $merger->addFile($backFilePath);
        $finalPdfContent = $merger->merge();

        // Guardar
        $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->put($pdfPath, $finalPdfContent);

        \Illuminate\Support\Facades\File::delete($frontFilePath, $backFilePath);

        \App\Models\Certificate::create([
            'course_id' => $request->course_id,
            'person_id' => $request->person_id,
            'condition' => $request->condition,
            'nota' => $request->nota,
            'unique_code' => $uniqueCode,
            'qr_path' => $qrPath,
            'pdf_path' => $pdfPath,
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
            // The import() method from the package executes the 'collection' method in our class.
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));
        } catch (Throwable $e) {
            return redirect()->route('certificates.index')
                ->with('import_errors', ['Hubo un error crítico durante la importación: ' . $e->getMessage()]);
        }

        $importedCount = $import->getImportedCount();
        $errors = $import->getErrors();

        $successMsg = "Proceso finalizado. Se importaron " . $importedCount . " certificados exitosamente.";

        // If there were errors, we send them to the view along with the success message.
        if (!empty($errors)) {
            return redirect()->route('certificates.index')
                ->with('success', $successMsg)
                ->with('import_errors', $errors);
        }

        // If everything was perfect, we just send the success message.
        return redirect()->route('certificates.index')->with('success', $successMsg);
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
        // Recuperamos los datos de la sesión
        $importData = session('import_data');

        // Si no hay datos en la sesión, redirigimos de vuelta
        if (empty($importData)) {
            return redirect()->route('certificates.import.form');
        }

        return view('certificates.preview', ['importData' => $importData]);
    }

    public function processImport()
    {
        $importData = session('import_data');

        if (empty($importData)) {
            return redirect()->route('certificates.import.form')->with('import_errors', ['No hay datos para importar.']);
        }

        $certificatesCreated = 0;

        // Iteramos sobre los datos validados y creamos los certificados
        foreach ($importData as $row) {
            // --- INICIO DE LA LÓGICA FALTANTE ---

            // 1. Buscar los modelos relacionados
            $course = Course::with('resolution')->where('nombre', $row['curso'])->first();
            $person = Person::firstOrCreate(
                ['dni' => $row['dni']],
                [
                    'apellido' => $row['apellido'],
                    'nombre'   => $row['nombre'],
                    'titulo'   => 'N/A',
                    'domicilio' => 'N/A',
                    'telefono'  => 'N/A',
                    'email'    => $row['dni'] . '@email-temporal.com',
                ]
            );

            // Si por alguna razón el curso no se encontrara, omitir
            if (!$course) {
                continue;
            }

            // 2. Generar QR y PDF (reutilizamos la lógica que ya funciona)
            $uniqueCode = $row['cuv'];
            $verificationUrl = route('certificates.verify', $uniqueCode);
            $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
            Storage::disk('public')->makeDirectory('qrcodes');
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

            $data = [
                'person' => $person,
                'course' => $course,
                'certificateData' => $row,
                'qr_path' => storage_path('app/public/' . $qrPath),
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf_template', $data)->setPaper('a4', 'landscape');
            $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
            Storage::disk('public')->makeDirectory('certificates');
            Storage::disk('public')->put($pdfPath, $pdf->output());

            // 3. Crear el registro del certificado con todos los datos
            Certificate::create([
                'course_id'       => $course->id,
                'person_id'       => $person->id,
                'condition'       => $row['tipo_de_certificado'] ?? 'Aprobado',
                'nota'            => $row['nota'] ?? null,
                'codigo_incremental' => $row['codigo_incremental'] ?? null,
                'anio'              => $row['ano'] ?? null,
                'tipo_certificado'  => $row['tipo_de_certificado'] ?? 'Aprobado',
                'iniciales'         => $row['iniciales'] ?? '',
                'tres_ultimos_digitos_dni' => $row['3ultimosdigitosdni'] ?? null,
                'unique_code'     => $uniqueCode,
                'qr_path'         => $qrPath,
                'pdf_path'        => $pdfPath,
            ]);

            // --- FIN DE LA LÓGICA FALTANTE ---

            $certificatesCreated++;
        }

        // Limpiamos los datos de la sesión
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
}
