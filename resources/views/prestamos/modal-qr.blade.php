{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         modal-qr.blade.php
 * @path         resources/views/prestamos/modal-qr.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-23
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modal del código QR de custodia: código de 250 px, contador de
 *               vigencia (rojo en los últimos 10 s) y capa "Código expirado"
 *               con "Generar nuevo". public/js/spei.js lo llena por POST.
 *
 * @requisitos   RF-034, RF-036, RN-004
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Modal QR); DT-028
 * ============================================================================
--}}
<dialog id="modal-qr" class="modal" aria-labelledby="modal-qr-titulo">
    <div class="modal__contenido">
        <div class="modal__cabecera">
            <div class="pila--2 titulos crece">
                <h2 id="modal-qr-titulo" class="modal__titulo" data-qr-titulo>Código QR</h2>
                <span class="mono texto-sec" data-qr-folio></span>
            </div>
            <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="qr">
            <div class="qr__imagen" data-qr-imagen role="img" aria-label="Código QR de un solo uso"></div>
            <div class="qr__cargando" data-qr-cargando hidden>
                <span class="icono icono--40" aria-hidden="true">progress_activity</span>
                <span class="visualmente-oculto">Generando código</span>
            </div>
            <div class="qr__expirado" data-qr-expirado hidden>
                <span class="icono icono--48 icono--rojo" aria-hidden="true">timer_off</span>
                <span class="texto-16 negrita">Código expirado</span>
                <button type="button" class="boton boton--primario" data-qr-regenerar>
                    <span class="icono" aria-hidden="true">refresh</span>Generar nuevo
                </button>
            </div>
        </div>
        <div class="qr__vigencia">
            <div class="encabezado-pagina texto-13">
                <span class="texto-sec">Vigencia del código</span>
                <span class="mono" data-qr-restante aria-live="off">0:00</span>
            </div>
            <div class="progreso progreso--6">
                <div class="progreso__valor progreso__valor--animado" data-qr-barra></div>
            </div>
        </div>
        <p class="qr__codigo" data-qr-codigo aria-label="Código para captura manual"></p>
        <p class="alerta alerta--error" data-qr-error role="alert" hidden></p>
        <div class="alerta alerta--fondo">
            <span class="icono icono--18" aria-hidden="true">shield</span>
            <span>Muéstralo en el mostrador. Es de un solo uso y expira en {{ max(30, min(60, (int) config('spei.qr.ttl'))) }} segundos por seguridad; si no hay lector, dicta el código de abajo.</span>
        </div>
    </div>
</dialog>
