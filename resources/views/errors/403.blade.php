{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         403.blade.php
 * @path         resources/views/errors/403.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página de error HTTP 403.
 *
 * @requisitos   RNF-008
 *
 * @see          resources/views/errors/plantilla.blade.php; DT-028
 * ============================================================================
--}}
@php($mensaje = trim((string) $exception->getMessage()))
@include('errors.plantilla', [
    'codigo' => 403,
    'icono' => 'lock',
    'titulo' => 'Acceso no permitido',
    'mensaje' => $mensaje !== '' && $mensaje !== 'This action is unauthorized.'
        ? $mensaje
        : 'No tiene permiso para realizar esta acción.',
])
