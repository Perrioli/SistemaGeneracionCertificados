@extends('adminlte::page')
@section('title', 'Crear Área')
@section('content_header')
    <h1>Crear Nueva Área</h1>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('areas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre del Área</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('areas.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop