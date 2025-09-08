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
                    [
                        'apellido' => $cleanedRow['apellido'] ?? 'N/A',
                        'nombre'   => $cleanedRow['nombre'] ?? 'N/A',
                        'titulo'   => 'N/A', 'domicilio' => 'N/A', 'telefono'  => 'N/A',
                        'email'    => $dni . '@email-temporal.com',
                    ]
                );

                $uniqueCode = $cuv;
                $verificationUrl = route('certificates.verify', $uniqueCode);
                $qrPath = 'qrcodes/' . $uniqueCode . '.svg';
                Storage::disk('public')->makeDirectory('qrcodes');
                QrCode::format('svg')->size(150)->generate($verificationUrl, storage_path('app/public/' . $qrPath));

                $data = [
                    'person' => $person, 'course' => $course,
                    'certificateData' => $cleanedRow, 'qr_path' => storage_path('app/public/' . $qrPath),
                ];

                $tempPath = storage_path('app/temp_pdf');
                File::ensureDirectoryExists($tempPath);

                $pdfFront = Pdf::loadView('certificates.pdf_template_front', $data)->setPaper('a4', 'landscape');
                $frontFilePath = $tempPath . '/' . $uniqueCode . '_front.pdf';
                $pdfFront->save($frontFilePath);

                $pdfBack = Pdf::loadView('certificates.pdf_template_back', $data)->setPaper('a4', 'landscape');
                $backFilePath = $tempPath . '/' . $uniqueCode . '_back.pdf';
                $pdfBack->save($backFilePath);

                $merger = new Merger;
                $merger->addFile($frontFilePath);
                $merger->addFile($backFilePath);
                $finalPdfContent = $merger->merge();
                
                $pdfPath = 'certificates/' . $uniqueCode . '.pdf';
                Storage::disk('public')->put($pdfPath, $finalPdfContent);
                
                File::delete($frontFilePath, $backFilePath);

                Certificate::create([
                    'course_id' => $course->id, 'person_id' => $person->id,
                    'condition' => (string) ($cleanedRow['tipo_de_certificado'] ?? 'Aprobado'),
                    'nota' => $cleanedRow['nota'] ?? null,
                    'codigo_incremental' => $cleanedRow['codigo_incremental'] ?? null,
                    'anio' => $cleanedRow['ano'] ?? null,
                    'tipo_certificado' => (string) ($cleanedRow['tipo_de_certificado'] ?? 'Aprobado'),
                    'iniciales' => (string) ($cleanedRow['iniciales'] ?? ''),
                    'tres_ultimos_digitos_dni' => $cleanedRow['3ultimosdigitosdni'] ?? null,
                    'unique_code' => $uniqueCode, 'qr_path' => $qrPath, 'pdf_path' => $pdfPath,
                ]);

                $this->importedCount++;

            } catch (Throwable $e) {
                $this->errors[] = "Error inesperado en la fila " . ($rowIndex + 2) . ": " . $e->getMessage();
                continue;
            }
        }
    }


    public function getErrors() { return $this->errors; }
    public function getImportedCount() { return $this->importedCount; }
}