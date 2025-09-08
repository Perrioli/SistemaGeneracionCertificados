@extends('adminlte::page')

@section('title', 'Editar Resolución')

@section('content_header')
    <h1>Editar Resolución</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('resolutions.update', $resolution->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <div class="form-group">
                    <label for="numero">Número de Resolución</label>
                    <input type="text" name="numero" class="form-control" id="numero" value="{{ old('numero', $resolution->numero) }}" required>
                </div>
                <div class="form-group">
                    <label for="ano">Año</label>
                    <input type="number" name="anio" class="form-control" id="anio" value="{{ old('ano', $resolution->ano) }}" required>
                </div>
                <div class="form-group">
                    <label for="area">Área Emisora</label>
                    <input type="text" name="area" class="form-control" id="area" value="{{ old('area', $resolution->area) }}" required>
                </div>
                <div class="form-group">
                    <label for="pdf_file">Archivo PDF de la Resolución</label>
                    <br>
                    <a href="{{ asset('storage/' . $resolution->pdf_path) }}" target="_blank">Ver PDF Actual</a>
                    <br><br>
                    <input type="file" name="pdf_file" class="form-control-file" id="pdf_file" accept=".pdf">
                    <small class="form-text text-muted">Sube un nuevo archivo solo si deseas reemplazar el actual.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('resolutions.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop