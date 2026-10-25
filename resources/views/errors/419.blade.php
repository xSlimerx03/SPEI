{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         419.blade.php
 * @path         resources/views/errors/419.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página de error HTTP 419.
 *
 * @requisitos   RNF-008
 *
 * @see          resources/views/errors/plantilla.blade.php; DT-028
 * ============================================================================
--}}
@include('errors.plantilla', [
    'codigo' => 419,
    'icono' => 'timer_off',
    'titulo' => 'La página caducó',
    'mensaje' => 'Tu sesión expiró por inactividad. Recarga la página e inténtalo de nuevo.',
])
