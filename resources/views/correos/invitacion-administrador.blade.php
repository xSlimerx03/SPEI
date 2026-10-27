{{--
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Suscripciones
 * @file         invitacion-administrador.blade.php
 * @path         resources/views/correos/invitacion-administrador.blade.php
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 * @license      Propietario - BigBitByte / Institución Educativa
 * @description  Invitación con enlace firmado para definir la contraseña de cualquier cuenta
 *               creada por un administrador; el propósito depende del rol.
 * @requisitos   RF-008, RF-016, RF-019, RF-020
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md
--}}
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Invitación al SPEI</title></head>
<body style="font-family: Calibri, Arial, sans-serif; color: #1F2937; line-height: 1.5;">
    <h1>Complete su registro</h1>
    <p>Hola, {{ $nombreInvitado }}.</p>
    <p>{{ $nombreInstitucion }} le invita a {{ $proposito }} en el SPEI.</p>
    <p>Defina su contraseña para activar su cuenta:</p>
    <p><a href="{{ $enlace }}">Completar registro</a></p>
    <p>El enlace vence {{ config('spei.invitacion.vigencia_horas') }} horas después de su emisión.</p>
    <p>Si no reconoce esta invitación, ignore este correo. No comparta el enlace.</p>
    <p>SPEI · BigBitByte</p>
</body>
</html>
