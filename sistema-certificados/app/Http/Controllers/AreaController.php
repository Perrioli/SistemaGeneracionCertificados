<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Facades\Blade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use iio\libmergepdf\Merger;
use stdClass;
use Throwable;


class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::latest()->paginate(10);
        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|unique:areas,nombre|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Area::create($data);

        return redirect()->route('areas.index')->with('success', 'Área creada exitosamente.');
    }

    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre,' . $area->id,
            'descripcion' => 'nullable|string',
            'template_front' => 'nullable|string',
            'template_back' => 'nullable|string',
        ]);

        $area->update($data);

        return redirect()->route('areas.index')->with('success', 'Área actualizada exitosamente.');
    }

    public function destroy(Area $area)
    {
        if ($area->courses()->count() > 0) {
            return redirect()->route('areas.index')
                ->with('error', 'No se puede eliminar esta área porque está siendo utilizada por uno o más cursos.');
        }


        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Área eliminada exitosamente.');
    }

    public function previewTemplate(Area $area)
    {
        $templateFront = $area->template_front;
        $templateBack = $area->template_back;

        if (empty($templateFront) || empty($templateBack)) {
            return "Esta área no tiene ambas plantillas (frente y reverso) definidas.";
        }

        $person = new \App\Models\Person([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'dni' => '12.345.678'
        ]);
        $course = new \App\Models\Course([
            'nombre' => 'Curso de Ejemplo',
            'horas' => 40
        ]);
        $course->area = $area; 

        $certificateData = [
            'cuv' => 'CUV-PREVIEW-12345',
            'tipo_de_certificado' => 'Aprobado',
            'ano' => date('Y'),
            'horas' => 40,
        ];

        $data = [
            'person' => $person,
            'course' => $course,
            'certificateData' => $certificateData,
            'qr_path' => public_path('images/logo.png'),
        ];

        try {
            $htmlFront = Blade::render($templateFront, $data);
            $htmlBack = Blade::render($templateBack, $data);

            $tempPath = storage_path('app/temp_pdf');
            File::ensureDirectoryExists($tempPath);
            $uniqueCode = 'preview_' . time();

            $pdfFront = Pdf::loadHTML($htmlFront)->setPaper('a4', 'landscape');
            $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
            $pdfFront->save($frontFilePath);

            $pdfBack = Pdf::loadHTML($htmlBack)->setPaper('a4', 'landscape');
            $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
            $pdfBack->save($backFilePath);

            $merger = new Merger;
            $merger->addFile($frontFilePath);
            $merger->addFile($backFilePath);
            $finalPdfContent = $merger->merge();

            File::delete($frontFilePath, $backFilePath);

            return response($finalPdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="previsualizacion.pdf"');
        } catch (Throwable $e) {
            return "Error al renderizar la plantilla: " . $e->getMessage();
        }
    }
}
