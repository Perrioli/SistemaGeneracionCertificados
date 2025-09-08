<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificado (Frente)</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; }
        .page { width: 1080px; height: 750px; position: relative; } /* A4 Landscape */
        .background { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
        
        .content-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            text-align: center;
        }

        .element {
            position: absolute;
            width: 90%;
            left: 5%;
            text-align: center;
        }
        .course-title { top: 100px; font-size: 32px; font-weight: bold; text-transform: uppercase; }
        
        .person-name-container { 
            top: 240px;
            background-color: #3498db;
            color: white;
            padding: 15px 0;
            width: 80%;
            left: 10%;
        }
        .person-name { font-size: 36px; font-weight: bold; margin: 0; }

        .main-text { top: 360px; font-size: 18px; line-height: 1.5; color: #333; }
        .certificate-type { top: 480px; font-size: 24px; font-weight: bold; color: #222;}
        
        /* Contenedores para los elementos del pie de página */
        .footer-element {
            position: absolute;
            bottom: 70px; /* Bajamos toda la sección del pie de página */
            width: 33.33%;
            text-align: center;
        }
        .signature-image { max-height: 50px; margin-bottom: 5px; }
        .signature-text { font-size: 12px; margin: 0; }
        .qr-image { width: 110px; height: 110px; }

    </style>
</head>
<body>
    <div class="page">
        <img src="{{ public_path('images/background.jpg') }}" class="background">
        <div class="content-overlay">

            <div class="element course-title">
                {{ strtoupper($course->nombre ?? '') }}
            </div>
            
            <div class="element person-name-container">
                <h1 class="person-name">{{ $person->nombre ?? '' }} {{ $person->apellido ?? '' }}</h1>
            </div>

            <div class="element main-text">
                <p>
                    Por haber <strong>{{ $certificateData['tipo_de_certificado'] ?? 'completado' }}</strong>
                    el curso de "{{ $course->area->nombre ?? 'Área no definida' }}",
                    a través del sistema de gestión de certificados, con fecha {{ date('d/m/Y') }},
                    con una duración de {{ $course->horas ?? 'N/A' }} horas, se le otorga el presente.
                </p>
            </div>

            <div class="element certificate-type">
                <h2>Certificado de {{ $certificateData['tipo_de_certificado'] ?? 'Participación' }}</h2>
            </div>

            <div class="footer-element" style="left: 0;">
                @if($course->signature1_path)
                    <img src="{{ storage_path('app/public/' . $course->signature1_path) }}" class="signature-image">
                    <p class="signature-text">{{ $course->capacitador_nombre ?? 'Firma 1' }}</p>
                @endif
            </div>

            <div class="footer-element" style="left: 33.33%;">
                 <img src="{{ $qr_path }}" class="qr-image">
            </div>

            <div class="footer-element" style="right: 0;">
                 @if($course->signature2_path)
                    <img src="{{ storage_path('app/public/' . $course->signature2_path) }}" class="signature-image">
                    <p class="signature-text">{{ $course->coordinador_nombre ?? 'Firma 2' }}</p>
                @endif
            </div>

        </div>
    </div>
</body>
</html>