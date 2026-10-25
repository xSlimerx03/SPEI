/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         spei.js
 * @path         public/js/spei.js
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.2.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Interactividad ligera con JavaScript nativo (DT-028): sidebar
 *               contraíble, desplegables, modales, toasts, mostrar contraseña,
 *               contadores de caracteres, envío automático de filtros, código
 *               QR con vigencia, asistente de solicitud y lector de cámara.
 *               Cada función es una mejora progresiva: sin JavaScript las
 *               pantallas siguen operando con formularios normales.
 *
 * @requisitos   RNF-008, RF-022, RF-034, RF-036
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md
 * ============================================================================
 */

(function () {
    'use strict';

    var CLAVE_LATERAL = 'spei.lateralContraido';

    /** Guarda una preferencia local sin fallar en modo privado. */
    function guardarPreferencia(clave, valor) {
        try {
            window.localStorage.setItem(clave, valor);
        } catch (error) {
            // NOTE: la preferencia es solo de comodidad; si no se guarda, no pasa nada.
        }
    }

    /** Token CSRF de la página. */
    function obtenerCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    /** Envía un formulario disparando su validación nativa cuando el navegador lo permite. */
    function enviarFormulario(formulario) {
        if (typeof formulario.requestSubmit === 'function') {
            formulario.requestSubmit();
            return;
        }

        formulario.submit();
    }

    /* ----- Sidebar contraíble (64 px / 240 px) ---------------------------- */
    function iniciarLateral() {
        var boton = document.querySelector('[data-contraer-lateral]');

        if (!boton) {
            return;
        }

        var actualizar = function () {
            var contraido = document.documentElement.classList.contains('lateral-contraido');
            boton.setAttribute('aria-expanded', contraido ? 'false' : 'true');
            boton.setAttribute('aria-label', contraido ? 'Expandir menú' : 'Contraer menú');
        };

        boton.addEventListener('click', function () {
            var contraido = document.documentElement.classList.toggle('lateral-contraido');
            guardarPreferencia(CLAVE_LATERAL, contraido ? '1' : '0');
            actualizar();
        });
        actualizar();
    }

    /* ----- Desplegables exclusivos entre sí ------------------------------- */
    function cerrarDesplegables(excepto) {
        document.querySelectorAll('[data-desplegable]').forEach(function (contenedor) {
            if (contenedor === excepto) {
                return;
            }

            var boton = contenedor.querySelector('[data-desplegable-boton]');
            var panel = contenedor.querySelector('[data-desplegable-panel]');

            if (boton && panel) {
                boton.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
            }
        });
    }

    function iniciarDesplegables() {
        document.querySelectorAll('[data-desplegable]').forEach(function (contenedor) {
            var boton = contenedor.querySelector('[data-desplegable-boton]');
            var panel = contenedor.querySelector('[data-desplegable-panel]');

            if (!boton || !panel) {
                return;
            }

            boton.addEventListener('click', function (evento) {
                evento.stopPropagation();
                var abrir = panel.hidden;
                cerrarDesplegables(contenedor);
                panel.hidden = !abrir;
                boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            });
        });

        document.addEventListener('click', function (evento) {
            if (!evento.target.closest('[data-desplegable]')) {
                cerrarDesplegables(null);
            }
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                cerrarDesplegables(null);
            }
        });
    }

    /* ----- Modales con <dialog> ------------------------------------------- */
    function iniciarModales() {
        document.querySelectorAll('[data-abrir-modal]').forEach(function (disparador) {
            disparador.addEventListener('click', function (evento) {
                var modal = document.getElementById(disparador.getAttribute('data-abrir-modal'));

                if (!modal || typeof modal.showModal !== 'function') {
                    return;
                }

                evento.preventDefault();
                modal.showModal();
                modal.dispatchEvent(new CustomEvent('spei:modal-abierto', { detail: { disparador: disparador } }));
            });
        });

        document.querySelectorAll('dialog.modal').forEach(function (modal) {
            modal.addEventListener('click', function (evento) {
                // Un clic en el fondo (fuera del contenido) cierra el modal.
                if (evento.target === modal) {
                    modal.close();
                }
            });
        });

        document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var modal = boton.closest('dialog');

                if (modal) {
                    modal.close();
                }
            });
        });

        // Si el servidor regresó con errores de un formulario modal, se reabre.
        var pendiente = document.querySelector('dialog.modal[data-abrir-al-cargar]');

        if (pendiente && typeof pendiente.showModal === 'function') {
            pendiente.showModal();
        }
    }

    /* ----- Toasts (3 s; información 5 s) ---------------------------------- */
    function cerrarToast(toast) {
        toast.classList.add('toast--saliendo');
        window.setTimeout(function () {
            toast.remove();
        }, 200);
    }

    function iniciarToasts() {
        document.querySelectorAll('.toast').forEach(function (toast) {
            var duracion = toast.classList.contains('toast--info') ? 5000 : 3000;
            var cerrar = toast.querySelector('.toast__cerrar');

            if (cerrar) {
                cerrar.addEventListener('click', function () {
                    cerrarToast(toast);
                });
            }

            if (!toast.hasAttribute('data-persistente')) {
                window.setTimeout(function () {
                    cerrarToast(toast);
                }, duracion);
            }
        });
    }

    /* ----- Mostrar u ocultar contraseña ----------------------------------- */
    function iniciarContrasenas() {
        document.querySelectorAll('[data-mostrar-contrasena]').forEach(function (boton) {
            var campo = document.getElementById(boton.getAttribute('data-mostrar-contrasena'));
            var icono = boton.querySelector('.icono');

            boton.hidden = false;
            boton.addEventListener('click', function () {
                var visible = campo.type === 'text';
                campo.type = visible ? 'password' : 'text';
                icono.textContent = visible ? 'visibility' : 'visibility_off';
                boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                boton.setAttribute('aria-pressed', visible ? 'false' : 'true');
            });
        });
    }

    /* ----- Mínimo de caracteres con contador ------------------------------ */
    function iniciarMinimos() {
        document.querySelectorAll('[data-minimo]').forEach(function (campo) {
            var minimo = parseInt(campo.getAttribute('data-minimo'), 10);
            var formulario = campo.closest('form');
            var contador = formulario ? formulario.querySelector('[data-contador]') : null;
            var boton = formulario ? formulario.querySelector('[data-requiere-minimo]') : null;

            var actualizar = function () {
                var largo = campo.value.trim().length;

                if (contador) {
                    contador.textContent = largo;
                }

                if (boton) {
                    boton.disabled = largo < minimo;
                }
            };

            campo.addEventListener('input', actualizar);
            actualizar();
        });
    }

    /* ----- Envío automático de filtros ------------------------------------ */
    function iniciarAutoenvio() {
        document.querySelectorAll('[data-autoenvio]').forEach(function (control) {
            control.addEventListener('change', function () {
                if (control.form) {
                    enviarFormulario(control.form);
                }
            });
        });
    }

    /* ----- Confirmación de acciones destructivas -------------------------- */
    function iniciarConfirmaciones() {
        document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                if (!window.confirm(formulario.getAttribute('data-confirmar'))) {
                    evento.preventDefault();
                }
            });
        });
    }

    /* ----- Imprimir comprobantes ------------------------------------------ */
    function iniciarImpresion() {
        document.querySelectorAll('[data-imprimir]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                window.print();
            });
        });
    }

    /* ----- Filtro local de recursos (nueva solicitud) --------------------- */
    function iniciarFiltroRecursos() {
        var campo = document.querySelector('[data-filtro-recursos]');

        if (!campo) {
            return;
        }

        campo.hidden = false;
        campo.closest('[data-filtro-contenedor]').hidden = false;
        campo.addEventListener('input', function () {
            var texto = campo.value.trim().toLowerCase();

            document.querySelectorAll('[data-recurso-elegible]').forEach(function (opcion) {
                var coincide = opcion.getAttribute('data-buscar').indexOf(texto) !== -1;
                opcion.hidden = texto !== '' && !coincide && !opcion.querySelector('input:checked');
            });
            document.querySelectorAll('[data-grupo-area]').forEach(function (grupo) {
                grupo.hidden = !grupo.querySelector('[data-recurso-elegible]:not([hidden])');
            });
        });
    }

    /* ----- Filas clicables de tablas -------------------------------------- */
    function iniciarFilasClicables() {
        document.querySelectorAll('tr[data-href]').forEach(function (fila) {
            fila.addEventListener('click', function (evento) {
                if (evento.target.closest('a, button, input, select, label, form')) {
                    return;
                }

                window.location.href = fila.getAttribute('data-href');
            });
        });
    }

    /* ----- Código QR con vigencia (RF-034, RF-036) ------------------------ */
    function formatearRestante(segundos) {
        var s = Math.max(0, segundos);

        return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
    }

    function limpiarQr(qr) {
        if (qr.temporizador) {
            window.clearInterval(qr.temporizador);
            qr.temporizador = null;
        }

        qr.imagen.innerHTML = '';
        qr.codigo.textContent = '';
    }

    function pintarCuentaQr(qr, fin, ttl) {
        var segundos = Math.max(0, Math.round((fin - Date.now()) / 1000));
        qr.restante.textContent = formatearRestante(segundos);
        qr.barra.style.width = Math.min(100, (segundos / ttl) * 100) + '%';
        qr.barra.classList.toggle('progreso__valor--rojo', segundos <= 10);

        if (segundos <= 0) {
            limpiarQr(qr);
            qr.expirado.hidden = false;
        }
    }

    function mostrarErrorQr(qr, mensaje) {
        qr.cargando.hidden = true;
        qr.error.textContent = mensaje;
        qr.error.hidden = false;
    }

    function mostrarCodigoQr(qr, datos) {
        qr.cargando.hidden = true;
        // El SVG lo genera el servidor (BaconQrCode) y no contiene datos del usuario.
        qr.imagen.innerHTML = datos.svg;
        qr.codigo.textContent = datos.codigo;

        var fin = new Date(datos.expira_en).getTime();
        pintarCuentaQr(qr, fin, datos.ttl);
        qr.temporizador = window.setInterval(function () {
            pintarCuentaQr(qr, fin, datos.ttl);
        }, 1000);
    }

    function solicitarQr(qr) {
        limpiarQr(qr);
        qr.expirado.hidden = true;
        qr.error.hidden = true;
        qr.cargando.hidden = false;

        fetch(qr.url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': obtenerCsrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                return { ok: respuesta.ok, datos: datos };
            });
        }).then(function (resultado) {
            if (!resultado.ok) {
                mostrarErrorQr(qr, resultado.datos.message || 'No se pudo generar el código. Intente de nuevo.');
                return;
            }

            mostrarCodigoQr(qr, resultado.datos);
        }).catch(function () {
            mostrarErrorQr(qr, 'No se pudo generar el código. Revise su conexión.');
        });
    }

    function abrirQr(qr, boton) {
        qr.url = boton.getAttribute('data-qr-url');
        qr.titulo.textContent = boton.getAttribute('data-qr-titulo');
        qr.folio.textContent = boton.getAttribute('data-qr-folio');
        qr.modal.showModal();
        solicitarQr(qr);
    }

    function iniciarQr() {
        var modal = document.getElementById('modal-qr');

        if (!modal || typeof modal.showModal !== 'function') {
            return;
        }

        var qr = { modal: modal, url: null, temporizador: null };
        ['imagen', 'codigo', 'restante', 'barra', 'expirado', 'cargando', 'error', 'titulo', 'folio']
            .forEach(function (nombre) {
                qr[nombre] = modal.querySelector('[data-qr-' + nombre + ']');
            });

        document.querySelectorAll('[data-qr-url]').forEach(function (boton) {
            boton.addEventListener('click', function (evento) {
                evento.preventDefault();
                abrirQr(qr, boton);
            });
        });

        modal.querySelector('[data-qr-regenerar]').addEventListener('click', function () {
            solicitarQr(qr);
        });
        modal.addEventListener('close', function () {
            limpiarQr(qr);
        });
    }

    /* ----- Asistente de nueva solicitud (3 pasos) ------------------------- */
    function obtenerRecursosElegidos(formulario) {
        return Array.prototype.slice.call(formulario.querySelectorAll('input[name="recursos[]"]:checked'));
    }

    function obtenerValor(formulario, nombre) {
        return formulario.querySelector('[name="' + nombre + '"]').value.trim();
    }

    function validarPasoRecursos(formulario) {
        var elegidos = obtenerRecursosElegidos(formulario);
        var areas = elegidos.map(function (recurso) {
            return recurso.getAttribute('data-area');
        }).filter(function (valor, indice, lista) {
            return lista.indexOf(valor) === indice;
        });

        if (elegidos.length === 0) {
            return 'Seleccione al menos un recurso.';
        }

        if (elegidos.length > 10 || areas.length > 1) {
            return 'Seleccione entre uno y diez recursos de una misma área.';
        }

        return null;
    }

    function validarPasoHorario(formulario) {
        var fecha = obtenerValor(formulario, 'fecha');
        var inicio = obtenerValor(formulario, 'hora_inicio');
        var fin = obtenerValor(formulario, 'hora_fin');

        if (!fecha) {
            return 'Elija el día del préstamo.';
        }

        if (!inicio || !fin || fin <= inicio) {
            return 'La hora de fin debe ser posterior a la hora de inicio.';
        }

        if (new Date(fecha + 'T' + inicio) < new Date(Date.now() - 60000)) {
            return 'Elija un horario futuro.';
        }

        if (obtenerValor(formulario, 'motivo').length < 5) {
            return 'Describa el motivo de uso (al menos 5 caracteres).';
        }

        return null;
    }

    function escribirResumen(formulario, clave, texto) {
        formulario.querySelectorAll('[data-resumen="' + clave + '"]').forEach(function (nodo) {
            nodo.textContent = texto;
        });
    }

    function actualizarResumenSolicitud(formulario) {
        var elegidos = obtenerRecursosElegidos(formulario);
        var nombres = elegidos.map(function (recurso) {
            return recurso.getAttribute('data-nombre');
        });
        var fecha = obtenerValor(formulario, 'fecha');
        var horario = obtenerValor(formulario, 'hora_inicio') + ' – ' + obtenerValor(formulario, 'hora_fin');
        var textoFecha = fecha ? new Date(fecha + 'T12:00').toLocaleDateString('es-MX', {
            weekday: 'long', day: 'numeric', month: 'long'
        }) : '';
        var cantidad = nombres.length === 1 ? 'El recurso está' : 'Los ' + nombres.length + ' recursos están';

        escribirResumen(formulario, 'recursos', nombres.join(' · '));
        escribirResumen(formulario, 'cantidad', cantidad);
        escribirResumen(formulario, 'fecha', textoFecha ? textoFecha + ' · ' + horario : '');
        escribirResumen(formulario, 'area', elegidos.length ? elegidos[0].getAttribute('data-area-nombre') : '');
    }

    function marcarIndicador(indicador, numero) {
        var n = parseInt(indicador.getAttribute('data-indicador-paso'), 10);
        indicador.classList.toggle('paso--activo', n === numero);
        indicador.classList.toggle('paso--hecho', n < numero);

        if (n === numero) {
            indicador.setAttribute('aria-current', 'step');
            return;
        }

        indicador.removeAttribute('aria-current');
    }

    function mostrarPaso(asistente, numero) {
        var total = asistente.pasos.length;
        asistente.actual = numero;
        asistente.pasos.forEach(function (paso) {
            paso.hidden = parseInt(paso.getAttribute('data-paso'), 10) !== numero;
        });
        asistente.indicadores.forEach(function (indicador) {
            marcarIndicador(indicador, numero);
        });
        asistente.anterior.hidden = numero === 1;
        asistente.siguiente.hidden = numero === total;
        asistente.enviar.hidden = numero !== total;
        asistente.aviso.hidden = true;
        actualizarResumenSolicitud(asistente.formulario);
    }

    function avanzarPaso(asistente) {
        var validadores = { 1: validarPasoRecursos, 2: validarPasoHorario };
        var validar = validadores[asistente.actual];
        var problema = validar ? validar(asistente.formulario) : null;

        if (problema) {
            asistente.aviso.querySelector('[data-paso-aviso-texto]').textContent = problema;
            asistente.aviso.hidden = false;
            return;
        }

        mostrarPaso(asistente, asistente.actual + 1);
        asistente.formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function iniciarSolicitud() {
        var formulario = document.querySelector('[data-solicitud]');

        if (!formulario) {
            return;
        }

        var asistente = {
            formulario: formulario,
            pasos: formulario.querySelectorAll('[data-paso]'),
            indicadores: document.querySelectorAll('[data-indicador-paso]'),
            anterior: formulario.querySelector('[data-paso-anterior]'),
            siguiente: formulario.querySelector('[data-paso-siguiente]'),
            enviar: formulario.querySelector('[data-paso-enviar]'),
            aviso: formulario.querySelector('[data-paso-aviso]'),
            actual: parseInt(formulario.getAttribute('data-paso-inicial') || '1', 10)
        };
        var actualizar = function () {
            actualizarResumenSolicitud(formulario);
        };

        asistente.siguiente.addEventListener('click', function () {
            avanzarPaso(asistente);
        });
        asistente.anterior.addEventListener('click', function () {
            mostrarPaso(asistente, asistente.actual - 1);
        });
        formulario.addEventListener('change', actualizar);
        formulario.setAttribute('data-js', '1');
        iniciarCalendario(formulario, actualizar);
        mostrarPaso(asistente, asistente.actual);
    }

    /* ----- Calendario mensual (lunes primero; pasados y domingos deshabilitados) */
    function formatearFechaIso(fecha) {
        return fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0') + '-'
            + String(fecha.getDate()).padStart(2, '0');
    }

    function crearDiaCalendario(calendario, fecha) {
        var iso = formatearFechaIso(fecha);
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'calendario__dia';
        boton.textContent = String(fecha.getDate());
        boton.setAttribute('data-fecha', iso);
        boton.setAttribute('aria-label', fecha.toLocaleDateString('es-MX', {
            weekday: 'long', day: 'numeric', month: 'long'
        }));
        boton.disabled = fecha < calendario.hoy || fecha > calendario.maximo || fecha.getDay() === 0;
        boton.classList.toggle('calendario__dia--hoy', iso === formatearFechaIso(calendario.hoy));

        if (calendario.campo.value === iso) {
            boton.classList.add('calendario__dia--elegido');
            boton.setAttribute('aria-pressed', 'true');
        }

        return boton;
    }

    function pintarCalendario(calendario) {
        var vista = calendario.vista;
        var desfase = (vista.getDay() + 6) % 7;
        var totalDias = new Date(vista.getFullYear(), vista.getMonth() + 1, 0).getDate();

        calendario.titulo.textContent = vista.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
        calendario.dias.innerHTML = '';

        for (var vacio = 0; vacio < desfase; vacio++) {
            calendario.dias.appendChild(document.createElement('span'));
        }

        for (var dia = 1; dia <= totalDias; dia++) {
            var fecha = new Date(vista.getFullYear(), vista.getMonth(), dia);
            calendario.dias.appendChild(crearDiaCalendario(calendario, fecha));
        }

        calendario.previo.disabled = vista.getFullYear() === calendario.hoy.getFullYear()
            && vista.getMonth() === calendario.hoy.getMonth();
        calendario.proximo.disabled = new Date(vista.getFullYear(), vista.getMonth() + 1, 1) > calendario.maximo;
    }

    function moverMes(calendario, meses) {
        calendario.vista.setMonth(calendario.vista.getMonth() + meses);
        pintarCalendario(calendario);
    }

    function iniciarCalendario(formulario, alCambiar) {
        var contenedor = formulario.querySelector('[data-calendario]');
        var campo = formulario.querySelector('[name="fecha"]');

        if (!contenedor || !campo) {
            return;
        }

        var hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        var calendario = {
            campo: campo,
            hoy: hoy,
            maximo: new Date(campo.getAttribute('max') + 'T00:00'),
            vista: campo.value ? new Date(campo.value + 'T00:00') : new Date(hoy),
            titulo: contenedor.querySelector('[data-calendario-mes]'),
            dias: contenedor.querySelector('[data-calendario-dias]'),
            previo: contenedor.querySelector('[data-calendario-previo]'),
            proximo: contenedor.querySelector('[data-calendario-proximo]')
        };
        calendario.vista.setDate(1);

        calendario.dias.addEventListener('click', function (evento) {
            var boton = evento.target.closest('[data-fecha]');

            if (!boton || boton.disabled) {
                return;
            }

            campo.value = boton.getAttribute('data-fecha');
            pintarCalendario(calendario);
            alCambiar();
        });
        calendario.previo.addEventListener('click', function () {
            moverMes(calendario, -1);
        });
        calendario.proximo.addEventListener('click', function () {
            moverMes(calendario, 1);
        });

        // El campo de fecha nativo se conserva para quien no usa el calendario.
        contenedor.hidden = false;
        campo.closest('[data-fecha-nativa]').hidden = true;
        pintarCalendario(calendario);
    }

    /* ----- Lector de QR con la cámara (si el navegador lo permite) -------- */
    function detenerCamara(escaner) {
        if (!escaner.flujo) {
            return;
        }

        escaner.flujo.getTracks().forEach(function (pista) {
            pista.stop();
        });
        escaner.flujo = null;
    }

    function leerCuadro(escaner) {
        if (!escaner.flujo) {
            return;
        }

        escaner.detector.detect(escaner.video).then(function (codigos) {
            var valor = codigos.length ? codigos[0].rawValue : '';

            if (!/^[a-f0-9]{32}$/.test(valor)) {
                window.requestAnimationFrame(function () {
                    leerCuadro(escaner);
                });
                return;
            }

            detenerCamara(escaner);
            escaner.campo.value = valor;
            enviarFormulario(escaner.campo.form);
        }).catch(function () {
            window.requestAnimationFrame(function () {
                leerCuadro(escaner);
            });
        });
    }

    function encenderCamara(escaner) {
        escaner.detector = new window.BarcodeDetector({ formats: ['qr_code'] });

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (flujo) {
            escaner.flujo = flujo;
            escaner.video.srcObject = flujo;
            escaner.video.hidden = false;
            escaner.leyenda.textContent = 'Apunte la cámara al código QR';
            escaner.video.play().then(function () {
                leerCuadro(escaner);
            });
        }).catch(function () {
            escaner.leyenda.textContent = 'No fue posible usar la cámara. Capture el código manualmente.';
        });
    }

    function iniciarEscaner() {
        var panel = document.querySelector('[data-escaner]');

        if (!panel || !('BarcodeDetector' in window) || !navigator.mediaDevices) {
            return;
        }

        var escaner = {
            boton: panel.querySelector('[data-escaner-iniciar]'),
            video: panel.querySelector('[data-escaner-video]'),
            campo: panel.querySelector('[data-escaner-campo]'),
            leyenda: panel.querySelector('[data-escaner-leyenda]'),
            detector: null,
            flujo: null
        };

        escaner.boton.hidden = false;
        escaner.boton.addEventListener('click', function () {
            encenderCamara(escaner);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        iniciarLateral();
        iniciarDesplegables();
        iniciarModales();
        iniciarToasts();
        iniciarContrasenas();
        iniciarMinimos();
        iniciarAutoenvio();
        iniciarConfirmaciones();
        iniciarFilasClicables();
        iniciarImpresion();
        iniciarFiltroRecursos();
        iniciarQr();
        iniciarSolicitud();
        iniciarEscaner();
    });
}());
