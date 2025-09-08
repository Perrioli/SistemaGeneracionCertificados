@extends('adminlte::page')

@section('title', 'Añadir Curso')

@section('content_header')
<h1>Añadir Nuevo Curso</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombre">Nombre del Curso</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="area_id">Área Emisora</label>
                    <select name="area_id" class="form-control" required>
                        @foreach ($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                        @endforeach
                    </select>
            </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="nro_curso">Nro. de Curso</label>
                <input type="text" name="nro_curso" class="form-control" value="{{ old('nro_curso') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="periodo">Período</label>
                <input type="text" name="periodo" class="form-control" placeholder="Ej. Marzo-Mayo 2025" value="{{ old('periodo') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="horas">Horas</label>
                <input type="number" name="horas" class="form-control" value="{{ old('horas') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="tipo_horas">Tipo de Horas</label>
                <select name="tipo_horas" class="form-control" required>
                    <option value="Reloj">Reloj</option>
                    <option value="Cátedra">Cátedra</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="resolution_id">Resolución Asociada</label>
                <select name="resolution_id" class="form-control" required>
                    <option value="">Seleccione una resolución</option>
                    @foreach ($resolutions as $resolution)
                    <option value="{{ $resolution->id }}">{{ $resolution->numero }} ({{ $resolution->anio }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="maxima_nota">Máxima Nota</label>
                <input type="number" name="maxima_nota" class="form-control" value="{{ old('maxima_nota') }}" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="objetivo">Objetivo</label>
        <textarea name="objetivo" class="form-control" rows="3">{{ old('objetivo') }}</textarea>
    </div>

    <div class="form-group">
        <label for="contenido">Contenido</label>
        <textarea name="contenido" class="form-control" rows="3">{{ old('contenido') }}</textarea>
    </div>

    <hr>
    <h5>Responsables y Firmas</h5>

    {{-- NOMBRES DE RESPONSABLES --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group"><label for="capacitador_nombre">Nombre del Capacitador (para firma 1)</label><input type="text" name="capacitador_nombre" class="form-control" value="{{ old('capacitador_nombre') }}"></div>
        </div>
        <div class="col-md-6">
            <div class="form-group"><label for="coordinador_nombre">Nombre del Coordinador (para firma 2)</label><input type="text" name="coordinador_nombre" class="form-control" value="{{ old('coordinador_nombre') }}"></div>
        </div>
    </div>

    {{-- ARCHIVOS DE FIRMAS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group"><label for="signature1">Subir Firma 1 (PNG)</label><input type="file" name="signature1" class="form-control-file" accept="image/png"></div>
        </div>
        <div class="col-md-6">
            <div class="form-group"><label for="signature2">Subir Firma 2 (PNG)</label><input type="file" name="signature2" class="form-control-file" accept="image/png"></div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Guardar Curso</button>
    <a href="{{ route('courses.index') }}" class="btn btn-secondary mt-4">Cancelar</a>
    </form>
</div>
</div>
@stop