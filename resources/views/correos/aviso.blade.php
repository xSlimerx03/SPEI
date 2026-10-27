{{--
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         aviso.blade.php
 * @path         resources/views/correos/aviso.blade.php
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 * @license      Propietario - BigBitByte / Institución Educativa
 * @description  Correo de aviso de préstamos, recordatorios y sanciones; el
 *               contenido se escapa (MP 3.3).
 * @requisitos   RF-045, RF-046, RF-048, RF-050
 * @see          CU-021
--}}
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>{{ $titulo }}</title></head>
<body style="font-family: Calibri, Arial, sans-serif; color: #1F2937; line-height: 1.5;">
    <h1>{{ $titulo }}</h1>
    <p>Hola, {{ $nombreDestinatario }}.</p>
    <p>{{ $mensaje }}</p>
    @if ($enlace)
        <p><a href="{{ $enlace }}">Abrir en el SPEI</a></p>
    @endif
    <p>Este aviso se envió automáticamente; no responda a este correo.</p>
    <p>SPEI · BigBitByte</p>
</body>
</html>
