@extends('adminlte::page')

@section('title', 'Importar Personas')

@section('content_header')
    <h1>Importar Personas desde Excel</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <p>Sube un archivo `.xlsx` para registrar múltiples personas en el sistema de una sola vez.</p>
            <p class="text-muted">La primera fila del archivo debe contener estas cabeceras exactamente:</p>
            <p><code>dni, apellido, nombre, titulo, domicilio, telefono, email</code></p>
            
            <form action="{{ route('persons.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="excel_file">Seleccionar Archivo Excel</label>
                    <input type="file" name="excel_file" class="form-control-file" id="excel_file" required accept=".xlsx, .xls">
                </div>
                
                <button type="submit" class="btn btn-primary">Importar Personas</button>
                <a href="{{ route('persons.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop