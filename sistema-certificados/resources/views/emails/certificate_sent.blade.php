<!DOCTYPE html>
<html>
<head>
    <title>Envío de Certificado</title>
</head>
<body>
    <h2>¡Felicitaciones, {{ $certificate->person->nombre }}!</h2>
    <p>Nos complace adjuntarte tu certificado por haber completado exitosamente el curso:</p>
    <p><strong>{{ $certificate->course->nombre }}</strong></p>
    <p>Puedes verificar su autenticidad en cualquier momento a través del código QR o en el siguiente enlace:</p>
    <a href="{{ route('certificates.verify', $certificate->unique_code) }}">Verificar mi Certificado</a>
    <br>
    <p>¡Gracias por participar!</p>
</body>
</html>