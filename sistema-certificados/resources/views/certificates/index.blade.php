@extends('adminlte::page')


@section('title', 'Gestión de Certificados')

@section('content_header')
    <h1>Listado de Certificados</h1>
@stop

@section('content')
    {{-- TARJETA PARA LOS FILTROS DE BÚSQUEDA (COLAPSADA POR DEFECTO) --}}
    <div class="card card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('certificates.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>DNI Persona</label>
                            <input type="text" name="search_dni" class="form-control" placeholder="Buscar por DNI..." value="{{ request('search_dni') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Curso</label>
                            <input type="text" name="search_course" class="form-control" placeholder="Buscar por Nombre del Curso..." value="{{ request('search_course') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="{{ route('certificates.index') }}" class="btn btn-secondary">Limpiar Filtros</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TARJETA PRINCIPAL CON LA TABLA DE CERTIFICADOS --}}
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
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DNI Persona</th>
                            <th>Nombre Persona</th>
                            <th>Curso</th>
                            <th>Área (Excel)</th>
                            <th>CUV</th>
                            <th style="width: 150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certificates as $certificate)
                        <tr>
                            <td>{{ $certificate->id }}</td>
                            <td>{{ $certificate->person->dni ?? 'N/A' }}</td>
                            <td>{{ $certificate->person->apellido ?? '' }}, {{ $certificate->person->nombre ?? '' }}</td>
                            <td>{{ $certificate->course->nombre ?? 'N/A' }}</td>
                            <td>{{ $certificate->area_excel ?? 'N/A' }}</td>
                            <td>{{ $certificate->unique_code }}</td>
                            <td class="d-flex align-items-center">
                                {{-- BOTONES DE ACCIÓN RESTAURADOS --}}
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
                            <td colspan="7" class="text-center">No hay certificados que coincidan con la búsqueda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $certificates->appends(request()->query())->links() }}
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