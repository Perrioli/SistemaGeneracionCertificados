@extends('adminlte::page')

@section('title', 'Gestión de Personas')

@section('content_header')
<h1>Listado de Personas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('persons.create') }}" class="btn btn-primary">Añadir Persona</a>
        {{-- El botón de "Importar desde Excel" ha sido eliminado de aquí --}}
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
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Apellido y Nombre</th>
                    <th>Título</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($people as $person)
                <tr>
                    <td>{{ $person->dni }}</td>
                    <td>{{ $person->apellido }}, {{ $person->nombre }}</td>
                    <td>{{ $person->titulo }}</td>
                    <td>{{ $person->email }}</td>
                    <td>{{ $person->telefono }}</td>
                    <td class="d-flex">
                        <a href="{{ route('persons.edit', $person->id) }}" class="btn btn-sm btn-info mr-2">Editar</a>
                        <form action="{{ route('persons.destroy', $person->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay personas registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $people->links() }}
    </div>
</div>
@stop

@section('js')
<script>
    $('.card-body').on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción enviará la persona a la papelera de reciclaje.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡elimínala!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endsection