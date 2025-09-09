@extends('adminlte::page')
@section('title', 'Emitir Nuevo Certificado')
@section('content_header')
<h1>Emitir Nuevo Certificado</h1>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('certificates.store') }}" method="POST">
            @csrf
            {{-- SELECCIÓN DE CURSO Y PERSONA --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label>Curso</label><select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- Seleccione un curso --</option>@foreach ($courses as $course)<option value="{{ $course->id }}">{{ $course->nombre }}</option>@endforeach
                        </select></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group"><label>Persona</label><select name="person_id" id="person_id" class="form-control" required>
                            <option value="">-- Seleccione una persona --</option>@foreach ($people as $person)<option value="{{ $person->id }}" data-person-id="{{ $person->id }}" data-dni="{{ $person->dni }}">{{ $person->apellido }}, {{ $person->nombre }}</option>@endforeach
                        </select></div>
                </div>
            </div>

            <hr>
            <h5>Datos para Generación de CUV</h5>

            {{-- NUEVOS CAMPOS --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group"><label>Unidad Académica</label><input type="text" name="unidad_academica" id="unidad_academica" class="form-control cuv-field" required></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Área</label><input type="text" id="area" class="form-control" readonly></div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"><label>Subárea</label><input type="text" name="subarea" id="subarea" class="form-control cuv-field" required></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group"><label>Código Incremental (ID Persona)</label><input type="text" id="codigo_incremental" class="form-control" readonly></div>
                </div>
                <div class="col-md-3">
                    <div class="form-group"><label>Año</label><input type="text" id="anio" class="form-control" value="{{ date('Y') }}" readonly></div>
                </div>
                <div class="col-md-3">
                    <div class="form-group"><label>Iniciales</label><input type="text" name="iniciales" id="iniciales" class="form-control cuv-field" required></div>
                </div>
                <div class="col-md-3">
                    <div class="form-group"><label>3 Últimos Dígitos del DNI</label><input type="text" id="tres_ultimos_dni" class="form-control" readonly></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label>Condición (Tipo Certificado)</label><select name="condition" id="condition" class="form-control cuv-field" required>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Asistente">Asistente</option>
                            <option value="Capacitador">Capacitador</option>
                        </select></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group"><label>Nota (opcional)</label><input type="number" step="0.01" name="nota" class="form-control"></div>
                </div>
            </div>

            {{-- CAMPO CUV (NO MODIFICABLE) --}}
            <div class="form-group">
                <label>CUV (Código Único de Verificación) Generado</label>
                @if($errors->has('cuv')) <div class="alert alert-danger">{{ $errors->first('cuv') }}</div> @endif
                <input type="text" id="cuv_display" class="form-control" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Generar Certificado</button>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Función para actualizar el CUV
        function updateCUV() {
            // Mapa para traducir la condición
            const conditionMap = {
                'Aprobado': 'APR',
                'Asistente': 'ASI',
                'Capacitador': 'CAP'
            };

            let unidad = $('#unidad_academica').val();
            let areaTextoCompleto = $('#area').val();
            let subarea = $('#subarea').val();
            let codigo = $('#codigo_incremental').val();
            let anio = $('#anio').val();

            let area = areaTextoCompleto.substring(0, 3).toUpperCase();
            let condicionTexto = $('#condition').val();
            let condicion = conditionMap[condicionTexto] || '';

            let iniciales = $('#iniciales').val();
            let ultimosDni = $('#tres_ultimos_dni').val();

            let cuv = unidad + area + subarea + codigo + anio + condicion + iniciales + ultimosDni;
            $('#cuv_display').val(cuv);
        }

        // Cuando se selecciona una PERSONA
        $('#person_id').on('change', function() {
            let selectedOption = $(this).find('option:selected');
            let personId = selectedOption.data('person-id');
            let dni = selectedOption.data('dni') ? String(selectedOption.data('dni')) : '';

            $('#codigo_incremental').val(personId);
            $('#tres_ultimos_dni').val(dni.slice(-3));
            updateCUV();
        });

        // Cuando se selecciona un CURSO
        $('#course_id').on('change', function() {
            let courseId = $(this).val();
            if (courseId) {
                $.ajax({
                    url: "{{ url('get-area-by-course') }}/" + courseId,
                    type: 'GET',
                    success: function(data) {
                        $('#area').val(data.area_name);
                        updateCUV();
                    }
                });
            } else {
                $('#area').val('');
                updateCUV();
            }
        });

        // Actualizar el CUV cuando cualquier otro campo cambie
        $('.cuv-field').on('keyup change', updateCUV);
    });
</script>
@endsection