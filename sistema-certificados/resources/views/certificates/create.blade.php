@extends('adminlte::page')

@section('title', 'Emitir Nuevo Certificado')

@section('content_header')
    <h1>Emitir Nuevo Certificado</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('certificates.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="course_id">Curso</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">-- Seleccione un curso --</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="person_id">Persona</label>
                    <select name="person_id" class="form-control" required>
                        <option value="">-- Seleccione una persona --</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}">{{ $person->apellido }}, {{ $person->nombre }} (DNI: {{ $person->dni }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="condition">Condición</label>
                            <select name="condition" class="form-control" required>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Asistente">Asistente</option>
                                <option value="Capacitador">Capacitador</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota">Nota (opcional)</label>
                            <input type="number" step="0.01" name="nota" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Generar Certificado</button>
            </form>
        </div>
    </div>
@stop