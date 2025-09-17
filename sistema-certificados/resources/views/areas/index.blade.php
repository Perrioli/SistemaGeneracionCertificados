@extends('adminlte::page')
@section('title', 'Gestión de Áreas')
@section('content_header')
<h1>Gestión de Áreas</h1>
@stop
@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('areas.create') }}" class="btn btn-primary">Crear Nueva Área</a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($areas as $area)
                <tr>
                    <td>{{ $area->nombre }}</td>
                    <td>{{ $area->descripcion }}</td>
                    <td class="d-flex">
                        <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-sm btn-info mr-2">Editar</a>
                        @if($area->template_front && $area->template_back)
                        <a href="{{ route('areas.preview', $area->id) }}" target="_blank" class="btn btn-sm btn-secondary mr-2">
                            Previsualizar
                        </a>
                        @endif
                        <form action="{{ route('areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop