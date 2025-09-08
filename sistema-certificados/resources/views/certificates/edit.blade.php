@extends('adminlte::page')

@section('title', 'Editar Certificado')

@section('content_header')
    <h1>Editar Certificado #{{ $certificate->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('certificates.update', $certificate->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="course_id">Curso</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">-- Seleccione un curso --</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id', $certificate->course_id) == $course->id)>
                                {{ $course->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="person_id">Persona</label>
                    <select name="person_id" class="form-control" required>
                        <option value="">-- Seleccione una persona --</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected(old('person_id', $certificate->person_id) == $person->id)>
                                {{ $person->apellido }}, {{ $person->nombre }} (DNI: {{ $person->dni }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Condición</label>
                            <select name="condition" class="form-control" required>
                                <option value="Aprobado" @selected(old('condition', $certificate->condition) == 'Aprobado')>Aprobado</option>
                                <option value="Asistente" @selected(old('condition', $certificate->condition) == 'Asistente')>Asistente</option>
                                <option value="Capacitador" @selected(old('condition', $certificate->condition) == 'Capacitador')>Capacitador</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota">Nota (opcional)</label>
                            <input type="number" step="0.01" name="nota" class="form-control" value="{{ old('nota', $certificate->nota) }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Certificado</button>
                <a href="{{ route('certificates.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop