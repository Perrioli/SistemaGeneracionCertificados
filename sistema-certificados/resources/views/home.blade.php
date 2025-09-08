@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Bienvenido al panel de administración del sistema de certificados.</p>

    {{-- Fila de Tarjetas de Resumen --}}
    <div class="row">
        {{-- Tarjeta de Resoluciones --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>...</h3>
                    <p>Resoluciones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('resolutions.index') }}" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Tarjeta de Cursos --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>...</h3>
                    <p>Cursos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <a href="{{ route('courses.index') }}" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Tarjeta de Personas (próximamente) --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>...</h3>
                    <p>Personas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- Tarjeta de Certificados (próximamente) --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>...</h3>
                    <p>Certificados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Gestionar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
@stop