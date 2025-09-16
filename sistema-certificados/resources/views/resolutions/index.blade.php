@extends('adminlte::page')

@section('title', 'Gestión de Resoluciones')

@section('content_header')
<h1>Listado de Resoluciones</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('resolutions.create') }}" class="btn btn-primary">Añadir Resolución</a>
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
                    <th style="width: 10px">#</th>
                    <th>Número</th>
                    <th>Año</th>
                    <th>Área</th>
                    <th>Archivo PDF</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resolutions as $resolution)
                <tr>
                    <td>{{ $resolution->id }}</td>
                    <td>{{ $resolution->numero }}</td>
                    <td>{{ $resolution->anio }}</td>
                    <td>{{ $resolution->area }}</td>
                    <td>
                        <a href="{{ asset('storage/' . $resolution->pdf_path) }}" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> Ver PDF
                        </a>
                    </td>
                    <td class="d-flex">
                        <a href="{{ route('resolutions.edit', $resolution->id) }}" class="btn btn-sm btn-info mr-2">Editar</a>

                        <form action="{{ route('resolutions.destroy', $resolution->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay resoluciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $resolutions->links() }}
    </div>
</div>
@stop

{{-- La sección de JavaScript DEBE ir FUERA de la sección de content --}}
@section('js')
<script>
    $('.card-body').on('click', '.btn-delete', function(e) {
        e.preventDefault();

        var form = $(this).closest('form');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "El archivo PDF asociado será eliminado permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡elimínalo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Confirmación Final',
                    html: `Para proceder, por favor escribe <strong>ELIMINAR</strong>.`,
                    input: 'text',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar Eliminación',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (value !== 'ELIMINAR') {
                            return 'La palabra no coincide. La eliminación ha sido cancelada.'
                        }
                    }
                }).then((result2) => {
                    if (result2.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });
    });
</script>
@endsection