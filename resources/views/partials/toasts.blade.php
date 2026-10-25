{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         toasts.blade.php
 * @path         resources/views/partials/toasts.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Toasts abajo a la derecha: éxito (3 s) e información (5 s).
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, Tabla 70; DT-028
 * ============================================================================
--}}
<div class="toasts" aria-live="polite">
    @if (session('success'))
        <div class="toast toast--exito" role="status">
            <span class="icono" aria-hidden="true">check_circle</span>
            <span class="toast__mensaje">{{ session('success') }}</span>
            <button type="button" class="toast__cerrar" aria-label="Cerrar mensaje">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
    @endif
    @if (session('info'))
        <div class="toast toast--info" role="status">
            <span class="icono" aria-hidden="true">info</span>
            <span class="toast__mensaje">{{ session('info') }}</span>
            <button type="button" class="toast__cerrar" aria-label="Cerrar mensaje">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
    @endif
</div>
