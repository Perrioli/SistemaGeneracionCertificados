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
            'NOMBRE',
            'APELLIDO',
            'CURSO',
            'NOTA',
            'UNIDAD ACADEMICA',
            'AREA',
            'SUBAREA',
            'CODIGO INCREMENTAL',
            'AÑO',
            'TIPO CERTIFICADO',
            'INICIALES',
            '3 ULTIMOS DEL DNI',
            'CUV'
        ];
    }
}
