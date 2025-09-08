<?php

namespace App\Imports;

use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class PersonsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithValidation
{
    /**
    * @param array $row
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Person([
            'dni'       => $row['dni'],
            'apellido'  => $row['apellido'],
            'nombre'    => $row['nombre'],
            'titulo'    => $row['titulo'],
            'domicilio' => $row['domicilio'],
            'telefono'  => $row['telefono'],
            'email'     => $row['email'],
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function rules(): array
    {

        return [
            '*.dni'   => ['required', 'string', 'unique:persons,dni'],
            '*.email' => ['required', 'email', 'unique:persons,email'],
        ];
    }
}