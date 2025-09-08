<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-t">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Certificados</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 100%;
            padding: 20px;
        }

        .header-links {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .header-links a {
            font-weight: 600;
            margin-left: 1.5rem;
            text-decoration: none;
            color: #007bff;
            font-size: 1rem;
        }

        .header-links a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .content h1 {
            font-size: 3.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .content p {
            font-size: 1.25rem;
            color: #6c757d;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="main-container">
        @if (Route::has('login'))
        <div class="header-links">
            @auth
            {{-- Si el usuario ya inició sesión, muestra un enlace al Dashboard --}}
            <a href="{{ url('/home') }}">Dashboard</a>
            @else
            {{-- Si el usuario es un visitante, solo muestra el enlace para Iniciar Sesión --}}
            <a href="{{ route('login') }}">Iniciar Sesión</a>
            @endauth
        </div>
        @endif

        <div class="content">
            <h1>Sistema de Gestión de Certificados</h1>
            <p>Una solución integral para la administración y emisión de certificados digitales.</p>
        </div>

        <div class="footer">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </div>
    </div>
</body>

</html>