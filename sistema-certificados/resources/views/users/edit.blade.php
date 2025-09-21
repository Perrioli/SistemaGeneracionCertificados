@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
<h1>Editar Usuario</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label for="role_id">Rol</label>
                <select name="role_id" id="role_id" class="form-control" required>
                    <option value="">Seleccione un rol</option>
                    @foreach ($roles as $role)
                    <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">
                        {{ $role->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="area-select-container" style="display: none;">
                <label for="area_id">Área (Solo para Administradores)</label>
                <select name="area_id" class="form-control">
                    <option value="">Seleccione un área</option>
                    @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(old('area_id', $user->area_id) == $area->id)>{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <hr>
            <p class="text-muted">Dejar en blanco si no se desea cambiar la contraseña.</p>
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop
@section('js')
<script>
    $(document).ready(function() {
        function toggleAreaSelect() {
            var selectedRoleName = $('#role_id').find('option:selected').data('role-name');
            if (selectedRoleName === 'Administrador' || selectedRoleName === 'Persona') {
                $('#area-select-container').show();
            } else {
                $('#area-select-container').hide();
                $('#area-select-container select').val('');
            }
        }
        toggleAreaSelect();
        $('#role_id').on('change', toggleAreaSelect);
    });
</script>
@endsection