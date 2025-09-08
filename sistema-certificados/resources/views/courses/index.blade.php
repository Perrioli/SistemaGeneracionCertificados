@extends('adminlte::page')

@section('title', 'Gestión de Cursos')

@section('content_header')
<h1>Listado de Cursos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('courses.create') }}" class="btn btn-primary">Añadir Curso</a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Área</th>
                    <th>Nro. Curso</th>
                    <th>Nombre</th>
                    <th>Período</th>
                    <th>Horas</th>
                    <th>Resolución</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                <tr>
                    <td>{{ $course->area->nombre ?? 'Sin Área' }}</td> 
                    <td>{{ $course->nro_curso }}</td>
                    <td>{{ $course->nombre }}</td>
                    <td>{{ $course->periodo }}</td>
                    <td>{{ $course->horas }} ({{ $course->tipo_horas }})</td>
                    <td>{{ $course->resolution->numero ?? 'N/A' }}</td>
                    <td class="d-flex">
                        <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-sm btn-info mr-2">Editar</a>
                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No hay cursos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $courses->links() }}
    </div>
</div>
@stop