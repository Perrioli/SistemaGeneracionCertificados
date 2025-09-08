@extends('adminlte::page')

@section('title', 'Previsualizar Importación')

@section('content_header')
<h1>Previsualizar y Confirmar Importación</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Se encontraron {{ count($importData) }} registros válidos para importar.</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Apellido y Nombre</th>
                    <th>Curso</th>
                    <th>CUV</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($importData as $row)
                <tr>
                    <td>{{ $row['dni'] }}</td>
                    <td>{{ $row['apellido'] }}, {{ $row['nombre'] }}</td>
                    <td>{{ $row['curso'] }}</td>
                    <td>{{ $row['cuv'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <a href="{{ route('certificates.import.preview_pdf') }}" target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> Previsualizar Certificado
        </a>
    </div>
    <div>
        <a href="{{ route('certificates.import.form') }}" class="btn btn-danger">Cancelar</a>
        <form action="{{ route('certificates.import.process') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">Confirmar e Importar {{ count($importData) }} Certificados</button>
        </form>
    </div>
</div>
</div>
@stop