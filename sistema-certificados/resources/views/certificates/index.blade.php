@extends('adminlte::page')

@section('title', 'Gestión de Certificados')

@section('content_header')
<h1>Listado de Certificados</h1>
@stop

@section('content')
<div class="card">
    @can('is-admin-or-root')
    <div class="card-header">
        <a href="{{ route('certificates.create') }}" class="btn btn-primary">Emitir Certificado</a>
        <a href="{{ route('certificates.import.form') }}" class="btn btn-success">Importar desde Excel</a>
    </div>
    @endcan
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('import_errors'))
        <div class="alert alert-danger">
            <h5 class="alert-heading">Se encontraron algunos errores durante la importación:</h5>
            <ul>
                @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Persona</th>
                    <th>Curso</th>
                    <th>Condición</th>
                    <th>Fecha Emisión</th>
                    <th style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($certificates as $certificate)
                <tr>
                    <td>{{ $certificate->id }}</td>
                    <td>{{ $certificate->person->apellido }}, {{ $certificate->person->nombre }}</td>
                    <td>{{ $certificate->course->nombre }}</td>
                    <td>{{ $certificate->condition }}</td>
                    <td>
                        {{ $certificate->created_at ? $certificate->created_at->format('d/m/Y') : 'Fecha no disponible' }}
                    </td>
                    <td class="d-flex align-items-center">
                        <a href="{{ asset('storage/' . $certificate->pdf_path) }}" target="_blank" class="btn btn-sm btn-info mr-2" title="Ver PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="{{ asset('storage/' . $certificate->qr_path) }}" target="_blank" class="btn btn-sm btn-secondary mr-2" title="Ver QR">
                            <i class="fas fa-qrcode"></i>
                        </a>
                        <a href="{{ route('certificates.edit', $certificate->id) }}" class="btn btn-sm btn-warning mr-2" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('certificates.destroy', $certificate->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-delete" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay certificados emitidos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $certificates->links() }}
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
            text: "El certificado PDF y su código QR serán eliminados permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
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