{{--
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Suscripciones
 * @file         alerta-vencimiento.blade.php
 * @path         resources/views/correos/alerta-vencimiento.blade.php
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 * @license      Propietario - BigBitByte / Institución Educativa
 * @description  Aviso de vencimiento con acceso al estado de la suscripción.
 * @requisitos   RF-005
 * @see          app/Mail/AlertaVencimientoMail.php
--}}
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Vencimiento de su suscripción</title></head>
<body style="font-family: Calibri, Arial, sans-serif; color: #1F2937; line-height: 1.5;">
    <h1>Su suscripción está por vencer</h1>
    <p>Hola, {{ $nombreDestinatario }}.</p>
    <p>{{ $mensaje }}</p>
    <p><a href="{{ route('suscripcion.show') }}">Ver suscripción</a></p>
    <p>SPEI · BigBitByte</p>
</body>
</html>
