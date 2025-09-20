<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Certificate;

class Person extends Model
{
    use HasFactory;

    protected $table = 'persons';

    protected $fillable = [
        'user_id',
        'dni',
        'apellido',
        'nombre',
        'titulo',
        'domicilio',
        'telefono',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class);
    }
}
