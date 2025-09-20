@extends('adminlte::page')

@section('title', 'Añadir Persona')

@section('content_header')
<h1>Añadir Nueva Persona</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('persons.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <input type="text" name="dni" class="form-control" value="{{ old('dni') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="domicilio">Domicilio</label>
                        <input type="text" name="domicilio" class="form-control" value="{{ old('domicilio') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                </div>

            </div>

            <div class="form-group">
                <label for="area_id">Área Asignada</label>
                <select name="area_id" class="form-control" required>
                    <option value="">Seleccione un Área</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}"
                        {{-- Para el formulario de edición, esto selecciona el área correcta --}}
                        @if(isset($person) && $person->area_id == $area->id) selected @endif
                        >
                        {{ $area->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('persons.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop