@extends('adminlte::page')
@section('title', 'Mi Panel')
@section('content_header')
    <h1>Mi Panel de Usuario</h1>
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <p>¡Bienvenido, {{ $user->name }}!</p>
            <p>Desde aquí podrás ver los cursos disponibles y tus certificados emitidos.</p>
            {{-- Aquí mostraremos la tabla con los certificados del usuario --}}
        </div>
    </div>
@stop