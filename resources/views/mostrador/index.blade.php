{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         index.blade.php
 * @path         resources/views/mostrador/index.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Mostrador (prototipo hi-fi): panel del escáner con cámara
 *               opcional y captura manual del código, resultado del escaneo
 *               con la condición de los recursos y agenda del día. El modo sin
 *               conexión no se ofrece hasta que exista (DT-022, DT-028).
 *
 * @requisitos   RF-034, RF-035, RF-036, RF-037
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Mostrador)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Mostrador')
@section('ruta', 'Entregas y devoluciones')

@section('contenido')
    <div class="pila--20 titulos">
        <div class="barra-filtros barra-filtros--tarjeta">
            <span class="texto-13 texto-apoyo en-linea en-linea--8">
                <span class="icono icono--18 icono--azul" aria-hidden="true">shield</span>
                Los códigos son de un solo uso y vencen a los {{ max(30, min(60, (int) config('spei.qr.ttl'))) }}
                segundos.
            </span>
        </div>

        @if ($sinArea)
            <div class="alerta alerta--advertencia alerta--grande" role="status">
                <span class="icono" aria-hidden="true">info</span>
                <span>
                    Tu cuenta de asistente aún no tiene un área asignada. Pide al administrador del sistema que te
                    asigne una para operar entregas.
                </span>
            </div>
        @endif

        <div class="cuadricula-paneles">
            <section class="escaner" data-escaner aria-labelledby="titulo-escaner">
                <div class="encabezado-pagina encabezado-pagina--centro">
                    <div class="titulos">
                        <h2 id="titulo-escaner" class="tarjeta__titulo">Escanear código QR</h2>
                        <span class="texto-13 texto-claro">Entrega o devolución · token de un solo uso</span>
                    </div>
                    <span class="icono texto-azul-claro" aria-hidden="true">qr_code_scanner</span>
                </div>
                <div class="escaner__vista">
                    <video class="escaner__video" data-escaner-video playsinline muted hidden></video>
                    <span class="escaner__leyenda" data-escaner-leyenda>vista de la cámara</span>
                    <div class="escaner__marco" aria-hidden="true">
                        <span class="escaner__esquina escaner__esquina--1"></span>
                        <span class="escaner__esquina escaner__esquina--2"></span>
                        <span class="escaner__esquina escaner__esquina--3"></span>
                        <span class="escaner__esquina escaner__esquina--4"></span>
                        <span class="escaner__linea"></span>
                    </div>
                </div>
                <button type="button" class="boton boton--grande boton--oscuro-primario" data-escaner-iniciar hidden>
                    <span class="icono" aria-hidden="true">photo_camera</span>Usar cámara
                </button>
                <form method="POST" action="{{ route('mostrador.validar') }}" class="en-linea en-linea--8 sin-envolver">
                    @csrf
                    <label for="codigo" class="visualmente-oculto">Código del QR</label>
                    <input type="text" id="codigo" name="codigo" class="control control--oscuro crece"
                           data-escaner-campo
                           placeholder="O ingresa el código del QR manualmente" maxlength="64" autocomplete="off"
                           spellcheck="false" required @if (! $resultado) autofocus @endif>
                    <button type="submit" class="boton boton--grande boton--oscuro-contorno">Validar</button>
                </form>
            </section>

            <div class="pila">
                @if ($errorCodigo)
                    <div class="alerta alerta--error alerta--grande" role="alert">
                        <span class="icono" aria-hidden="true">gpp_bad</span>
                        <div class="titulos">
                            <span class="alerta__titulo">Código rechazado</span>
                            <span>{{ $errorCodigo }}</span>
                        </div>
                    </div>
                @endif

                @if ($resultado)
                    @include('mostrador.resultado', [
                        'prestamo' => $resultado['prestamo'],
                        'token' => $resultado['token'],
                        'codigo' => $codigo,
                    ])
                @endif

                <section class="tarjeta" aria-labelledby="titulo-agenda">
                    <div class="tarjeta__cabecera">
                        <div class="titulos">
                            <h2 id="titulo-agenda" class="tarjeta__titulo">Agenda de hoy</h2>
                            <span class="tarjeta__subtitulo">
                                {{ count($agenda) }} {{ count($agenda) === 1 ? 'movimiento' : 'movimientos' }}
                            </span>
                        </div>
                    </div>
                    @include('mostrador.agenda', ['agenda' => $agenda, 'conIcono' => true])
                </section>
            </div>
        </div>
    </div>
@endsection
