{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         429.blade.php
 * @path         resources/views/errors/429.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página de error HTTP 429.
 *
 * @requisitos   RNF-008
 *
 * @see          resources/views/errors/plantilla.blade.php; DT-028
 * ============================================================================
--}}
@include('errors.plantilla', [
    'codigo' => 429,
    'icono' => 'hourglass_top',
    'titulo' => 'Demasiadas solicitudes',
    'mensaje' => 'Demasiados intentos. Espera un momento antes de intentarlo de nuevo.',
])
