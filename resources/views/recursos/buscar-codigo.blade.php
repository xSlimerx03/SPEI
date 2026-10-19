{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         buscar-codigo.blade.php
 * @path         resources/views/recursos/buscar-codigo.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Campo para el lector de código de barras tipo teclado: el
 *               lector escribe el código y envía Enter.
 *
 * @requisitos   RF-011
 *
 * @see          docs/requisitos/BBB_CU007_RegistrarRecurso_v1.0.md (pasos 4–5)
 * ============================================================================
--}}
<section class="tarjeta" aria-labelledby="titulo-codigo">
    <h2 id="titulo-codigo" class="tarjeta__titulo">Buscar por código de barras</h2>
    <form method="GET" action="{{ route('recursos.buscar') }}" class="fila-campos">
        <div class="campo">
            <label for="codigo" class="campo__etiqueta">Número de inventario o de serie</label>
            <input type="text" id="codigo" name="codigo" class="control" maxlength="100" required
                   autocomplete="off" aria-describedby="ayuda-codigo">
            <span id="ayuda-codigo" class="campo__ayuda">Coloque el cursor aquí y escanee la etiqueta.</span>
        </div>
        <div class="campo" style="justify-content: center;">
            <button type="submit" class="boton">
                <span class="icono icono--boton" aria-hidden="true">search</span>Buscar
            </button>
        </div>
    </form>
</section>
