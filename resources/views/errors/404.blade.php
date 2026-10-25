{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         404.blade.php
 * @path         resources/views/errors/404.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página de error HTTP 404.
 *
 * @requisitos   RNF-008
 *
 * @see          resources/views/errors/plantilla.blade.php; DT-028
 * ============================================================================
--}}
@include('errors.plantilla', [
    'codigo' => 404,
    'icono' => 'search_off',
    'titulo' => 'Página no encontrada',
    'mensaje' => 'El recurso que buscas no existe o no pertenece a tu institución.',
])
