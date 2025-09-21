@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content_header')
<h1>Mi Perfil</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completa o actualiza tus datos personales</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('persona.profile.update') }}" method="POST">

            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group"><label>DNI</label><input type="text" name="dni" class="form-control" value="{{ old('dni', $person->dni) }}" required></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Apellido</label><input type="text" name="apellido" class="form-control" value="{{ old('apellido', $person->apellido) }}" required></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" class="form-control" value="{{ old('nombre', $person->nombre) }}" required></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label>Título</label><input type="text" name="titulo" class="form-control" value="{{ old('titulo', $person->titulo) }}" required></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group"><label>Domicilio</label><input type="text" name="domicilio" class="form-control" value="{{ old('domicilio', $person->domicilio) }}" required></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" class="form-control" value="{{ old('telefono', $person->telefono) }}" required></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group"><label>Email (no editable)</label><input type="email" class="form-control" value="{{ $person->email }}" disabled></div>
                </div>
            </div>
            <div class="card card-outline card-secondary collapsed-card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Cambiar Contraseña</h3>

                    {{-- Herramientas de la tarjeta que contienen el botón para plegar/desplegar --}}
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password">Contraseña Actual</label>
                            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
                            @error('current_password', 'updatePassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Nueva Contraseña</label>
                            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
                            @error('password', 'updatePassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Contraseña</button>
                    </form>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>
@stop