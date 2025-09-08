<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CertificateTemplateExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Devolvemos una colección vacía porque solo queremos las cabeceras.
        return collect([]);
    }

    /**
     * @return array
     */
    // app/Exports/CertificateTemplateExport.php

    public function headings(): array
    {
        return [
            'DNI',
            'Nombre',
            'Apellido',
            'Curso',
            'Nota',
            'Codigo Incremental',
            'Año',
            'Tipo de certificado',
            'Iniciales',
            '3UltimosDigitosDni',
            'CUV',
        ];
    }
}
