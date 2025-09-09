<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'person_id',
        'condition', // 'Tipo de certificado'
        'nota',
        'codigo_incremental',
        'anio',
        'tipo_certificado',
        'iniciales',
        'tres_ultimos_digitos_dni',
        'unique_code', // nuestro 'CUV'
        'qr_path',
        'pdf_path',
        'unidad_academica', 
        'area_excel',  
        'subarea',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
