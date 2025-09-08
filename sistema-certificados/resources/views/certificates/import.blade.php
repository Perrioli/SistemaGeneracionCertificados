@extends('adminlte::page')

@section('title', 'Importar Certificados')

@section('content_header')
<h1>Importar Certificados desde Excel</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-4">
            <h5>Instrucciones</h5>
            <p>Para asegurar una importación exitosa, por favor descarga nuestra plantilla oficial. Esto garantiza que todos los nombres de las columnas sean correctos.</p>
            <a href="{{ route('certificates.template.download') }}" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Descargar Plantilla
            </a>
        </div>
        <hr>

        <p>Una vez que hayas llenado la plantilla, súbela usando el siguiente formulario:</p>

        <form action="{{ route('certificates.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="excel_file">Seleccionar Archivo Excel</label>
                <input type="file" name="excel_file" class="form-control-file" id="excel_file" required accept=".xlsx, .xls">
            </div>

            <button type="submit" class="btn btn-primary">Iniciar Importación</button>
            <a href="{{ route('certificates.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop