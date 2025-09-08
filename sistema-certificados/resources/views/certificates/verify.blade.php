<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Certificado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f4; }
        .verification-card { max-width: 600px; margin: 50px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card verification-card">
            <div class="card-header bg-success text-white text-center">
                <h3>✓ Certificado Válido</h3>
            </div>
            <div class="card-body">
                <p><strong>Código:</strong> {{ $certificate->unique_code }}</p>
                <p><strong>Nombre:</strong> {{ $certificate->person->nombre }} {{ $certificate->person->apellido }}</p>
                <p><strong>Curso:</strong> {{ $certificate->course->nombre }}</p>
                <p><strong>Condición:</strong> {{ $certificate->condition }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ $certificate->created_at->format('d/m/Y') }}</p>
                <div class="text-center mt-4">
                    <a href="{{ asset('storage/' . $certificate->pdf_path) }}" class="btn btn-primary" target="_blank">Descargar PDF</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>