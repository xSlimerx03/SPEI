{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         marca-acceso.blade.php
 * @path         resources/views/partials/marca-acceso.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Logotipo y nombre del producto en las tarjetas públicas.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, sección 2.3 (logotipo); DT-028
 * ============================================================================
--}}
<div class="acceso__marca">
    <img src="{{ asset('img/logo-bigbitbyte.png') }}" alt="BigBitByte" class="acceso__logo" width="48" height="48">
    <div class="titulos">
        <span class="acceso__producto">SPEI</span>
        <span class="texto-12 texto-sec">por Bigbitbyte</span>
    </div>
</div>
