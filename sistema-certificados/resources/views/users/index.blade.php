@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
<h1>Gestión de Usuarios</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('users.create') }}" class="btn btn-primary">Crear Nuevo Usuario</a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{-- Si el rol existe, muestra el nombre. Si no, muestra 'Sin Rol'. --}}
                        <span class="badge badge-info">{{ $user->role->name ?? 'Sin Rol' }}</span>
                    </td>
                    <td class="d-flex">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info mr-2">Editar</a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?');">
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