<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'area_id',
        'resolution_id',
        'nro_curso',
        'nombre',
        'periodo',
        'horas',
        'tipo_horas',
        'objetivo',
        'contenido',
        'maxima_nota',
        'signature1_path',
        'signature2_path',
        'capacitador_nombre',
        'coordinador_nombre'
    ];


    public function resolution(): BelongsTo
    {
        return $this->belongsTo(Resolution::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
