@extends('adminlte::page')
@section('title', 'Editar Área')
@section('content_header')
<h1>Editar Área</h1>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('areas.update', $area->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nombre">Nombre del Área</label>
                <input type="text" name="nombre" class="form-control" value="{{ $area->nombre }}" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ $area->descripcion }}</textarea>
            </div>
            <hr>
            <h5>Plantillas de Certificado (HTML)</h5>
            <div class="form-group">
                <label for="template_front">Plantilla del Frente</label>
                <textarea name="template_front" class="form-control" rows="10">{{ old('template_front', $area->template_front) }}</textarea>
            </div>
            <div class="form-group">
                <label for="template_back">Plantilla del Reverso</label>
                <textarea name="template_back" class="form-control" rows="10">{{ old('template_back', $area->template_back) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('areas.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop