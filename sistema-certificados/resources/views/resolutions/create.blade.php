@extends('adminlte::page')

@section('title', 'Añadir Resolución')

@section('content_header')
<h1>Añadir Nueva Resolución</h1>
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

        <form action="{{ route('resolutions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="numero">Número de Resolución</label>
                <input type="text" name="numero" class="form-control" id="numero" placeholder="Ej. 123/2024" value="{{ old('numero') }}" required>
            </div>
            <div class="form-group">
                <label for="ano">Año</label>
                <input type="number" name="anio" class="form-control" id="anio" placeholder="Ej. 2024" value="{{ old('ano') }}" required>
            </div>
            <div class="form-group">
                <label for="area_id">Área</label>
                <select name="area_id" class="form-control" required>
                    <option value="">Seleccione un Área</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}"
                        {{-- Para el formulario de edición, esto selecciona el área correcta --}}
                        @if(isset($resolution) && $resolution->area_id == $area->id) selected @endif
                        >
                        {{ $area->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="pdf_file">Archivo PDF de la Resolución</label>
                <input type="file" name="pdf_file" class="form-control-file" id="pdf_file" required accept=".pdf">
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button> <a href="{{ route('resolutions.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop