@extends('adminlte::page')

@section('title', 'Editar Curso')

@section('content_header')
    <h1>Editar Curso</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                {{-- DATOS PRINCIPALES --}}
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="nombre">Nombre del Curso</label><input type="text" name="nombre" class="form-control" value="{{ old('nombre', $course->nombre) }}" required></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="area_id">Área Emisora</label><select name="area_id" class="form-control" required><option value="">Seleccione un área</option>@foreach ($areas as $area)<option value="{{ $area->id }}" @selected(old('area_id', $course->area_id) == $area->id)>{{ $area->nombre }}</option>@endforeach</select></div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label for="nro_curso">Nro. de Curso</label><input type="text" name="nro_curso" class="form-control" value="{{ old('nro_curso', $course->nro_curso) }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="periodo">Período</label><input type="text" name="periodo" class="form-control" value="{{ old('periodo', $course->periodo) }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="horas">Horas</label><input type="number" name="horas" class="form-control" value="{{ old('horas', $course->horas) }}" required></div></div>
                    <div class="col-md-3"><div class="form-group"><label for="tipo_horas">Tipo de Horas</label><select name="tipo_horas" class="form-control" required><option value="Reloj" @selected(old('tipo_horas', $course->tipo_horas) == 'Reloj')>Reloj</option><option value="Cátedra" @selected(old('tipo_horas', $course->tipo_horas) == 'Cátedra')>Cátedra</option></select></div></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="resolution_id">Resolución Asociada</label><select name="resolution_id" class="form-control" required><option value="">Seleccione una resolución</option>@foreach ($resolutions as $resolution)<option value="{{ $resolution->id }}" @selected(old('resolution_id', $course->resolution_id) == $resolution->id)>{{ $resolution->numero }} ({{ $resolution->anio }})</option>@endforeach</select></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="maxima_nota">Máxima Nota</label><input type="number" name="maxima_nota" class="form-control" value="{{ old('maxima_nota', $course->maxima_nota) }}" required></div></div>
                </div>
                <div class="form-group"><label for="objetivo">Objetivo</label><textarea name="objetivo" class="form-control" rows="3">{{ old('objetivo', $course->objetivo) }}</textarea></div>
                <div class="form-group"><label for="contenido">Contenido</label><textarea name="contenido" class="form-control" rows="3">{{ old('contenido', $course->contenido) }}</textarea></div>

                <hr>
                <h5>Responsables y Firmas</h5>
                
                {{-- NOMBRES DE RESPONSABLES --}}
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="capacitador_nombre">Nombre del Capacitador (para firma 1)</label><input type="text" name="capacitador_nombre" class="form-control" value="{{ old('capacitador_nombre', $course->capacitador_nombre) }}"></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="coordinador_nombre">Nombre del Coordinador (para firma 2)</label><input type="text" name="coordinador_nombre" class="form-control" value="{{ old('coordinador_nombre', $course->coordinador_nombre) }}"></div></div>
                </div>

                {{-- ARCHIVOS DE FIRMAS --}}
                <p class="text-muted small">Sube un archivo nuevo solo si deseas reemplazar el actual.</p>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="signature1">Subir Firma 1 (PNG)</label><input type="file" name="signature1" class="form-control-file" accept="image/png">@if($course->signature1_path)<div class="mt-2"><label>Firma Actual:</label><br><img src="{{ asset('storage/' . $course->signature1_path) }}" alt="Firma 1" height="50"></div>@endif</div></div>
                    <div class="col-md-6"><div class="form-group"><label for="signature2">Subir Firma 2 (PNG)</label><input type="file" name="signature2" class="form-control-file" accept="image/png">@if($course->signature2_path)<div class="mt-2"><label>Firma Actual:</label><br><img src="{{ asset('storage/' . $course->signature2_path) }}" alt="Firma 2" height="50"></div>@endif</div></div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Actualizar Curso</button>
                <a href="{{ route('courses.index') }}" class="btn btn-secondary mt-4">Cancelar</a>
            </form>
        </div>
    </div>
@stop