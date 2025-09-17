<?php

namespace App\Imports;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;
use iio\libmergepdf\Merger;
use Throwable;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificateSent;
use Illuminate\Support\Facades\Blade;

class CertificatesImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    private $errors = [];
    private $importedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            try {
                $cleanedRow = [];
                foreach ($row as $key => $value) {
                    if ($key) {
                        $newKey = strtolower(str_replace([' ', '-'], '_', trim($key)));
                        $cleanedRow[$newKey] = $value;
                    }
                }

                $tipoCertificadoShort = strtoupper(trim($cleanedRow['tipo_certificado'] ?? ''));
                $conditionMap = [
                    'APR' => 'Aprobado',
                    'ASI' => 'Asistente',
                    'CAP' => 'Capacitador',
                ];
                $condition = $conditionMap[$tipoCertificadoShort] ?? 'Asistente';

                $cursoNombre = $cleanedRow['curso'] ?? null;
                $cuv = $cleanedRow['cuv'] ?? null;
                $dni = $cleanedRow['dni'] ?? null;

                if (empty($cursoNombre) || empty($cuv) || empty($dni)) {
                    $this->errors[] = "Error en la fila " . ($rowIndex + 2) . ": Faltan datos esenciales (DNI, Curso o CUV).";
                    continue;
                }

                $course = Course::with(['resolution', 'area'])->where('nombre', $cursoNombre)->first();
                if (!$course) {
                    $this->errors[] = "Error en la fila " . ($rowIndex + 2) . ": El curso '" . $cursoNombre . "' no fue encontrado.";
                    continue;
                }

                if (Certificate::where('unique_code', $cuv)->exists()) {
                    $this->errors[] = "Error en la fila " . ($rowIndex + 2) . ": El CUV '" . $cuv . "' ya existe.";
                    continue;
                }

                $person = Person::firstOrCreate(
                    ['dni' => $dni],
                    ['apellido' => $cleanedRow['apellido'] ?? 'N/A', 'nombre' => $cleanedRow['nombre'] ?? 'N/A', 'titulo' => 'N/A', 'domicilio' => 'N/A', 'telefono' => 'N/A', 'email' => $dni . '@email-temporal.com']
                );

                $uniqueCode = $cuv;
                $verificationUrl = route('certificates.verify', $uniqueCode);
                $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
                Storage::disk('public')->makeDirectory('qrcodes');
                QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

                $data = [
                    'person' => $person,
                    'course' => $course,
                    'certificateData' => $cleanedRow,
                    'qr_path' => storage_path('app/public/' . $qrPath),
                ];

                $templateFront = $course->area->template_front;
                $templateBack = $course->area->template_back;

                if (empty($templateFront) || empty($templateBack)) {
                    $this->errors[] = "Fila " . ($rowIndex + 2) . ": El Área '" . $course->area->nombre . "' no tiene plantillas de certificado definidas.";
                    continue;
                }

                $htmlFront = Blade::render($templateFront, $data);
                $htmlBack = Blade::render($templateBack, $data);

                $tempPath = storage_path('app/temp_pdf');
                File::ensureDirectoryExists($tempPath);

                $pdfFront = Pdf::loadHTML($htmlFront)->setPaper('a4', 'landscape');
                $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
                $pdfFront->save($frontFilePath);

                $pdfBack = Pdf::loadHTML($htmlBack)->setPaper('a4', 'landscape');
                $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
                $pdfBack->save($backFilePath);
                
                unset($pdfFront, $pdfBack);

                $merger = new Merger;
                $merger->addFile($frontFilePath);
                $merger->addFile($backFilePath);
                $finalPdfContent = $merger->merge();

                $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
                Storage::disk('public')->put($pdfPath, $finalPdfContent);

                File::delete($frontFilePath, $backFilePath);
                unset($finalPdfContent);

                $certificate = Certificate::create([
                    'course_id'       => $course->id,
                    'person_id'       => $person->id,
                    'condition'       => $condition,
                    'nota'            => $cleanedRow['nota'] ?? null,
                    'unidad_academica' => $cleanedRow['unidad_academica'] ?? null,
                    'area_excel'      => $cleanedRow['area'] ?? null,
                    'subarea'         => $cleanedRow['subarea'] ?? null,
                    'codigo_incremental' => $cleanedRow['codigo_incremental'] ?? null,
                    'anio'              => $cleanedRow['ano'] ?? null,
                    'tipo_certificado'  => $tipoCertificadoShort,
                    'iniciales'         => $cleanedRow['iniciales'] ?? null,
                    'tres_ultimos_digitos_dni' => $cleanedRow['3_ultimos_del_dni'] ?? null,
                    'unique_code'     => $uniqueCode,
                    'qr_path'         => $qrPath,
                    'pdf_path'        => $pdfPath,
                ]);

                if ($person->email && !str_ends_with($person->email, '@email-temporal.com')) {
                    try {
                        Mail::to($person->email)->send(new CertificateSent($certificate));
                    } catch (Throwable $e) {
                        $this->errors[] = "Fila " . ($rowIndex + 2) . ": Certificado creado, pero falló el envío por email.";
                    }
                }

                $this->importedCount++;
            } catch (Throwable $e) {
                $this->errors[] = "Error inesperado en la fila " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function getErrors() { return $this->errors; }
    public function getImportedCount() { return $this->importedCount; }
}