{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         token.blade.php
 * @path         resources/views/prestamos/token.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Página del código QR para navegadores sin JavaScript. Las
 *               variables vienen de SolicitudController::token().
 *
 * @requisitos   RF-034, RF-036
 *
 * @see          resources/views/prestamos/modal-qr.blade.php; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', $tipo === 'entrega' ? 'QR de entrega' : 'QR de devolución')
@section('ruta', 'Mis préstamos')

@section('contenido')
    <div class="contenedor-720">
        <div class="tarjeta tarjeta--relleno-24 modal__contenido">
            <div class="pila--2 titulos">
                <span class="subtitulo-pagina">{{ $tipo === 'entrega' ? 'QR de entrega' : 'QR de devolución' }}</span>
                <span class="mono texto-sec">{{ $prestamo->obtenerFolio() }}</span>
            </div>
            <div class="qr">
                <div class="qr__imagen" role="img" aria-label="Código QR de un solo uso">{!! $svg !!}</div>
            </div>
            <p class="qr__codigo">{{ $codigo }}</p>
            <div class="alerta alerta--fondo">
                <span class="icono icono--18" aria-hidden="true">shield</span>
                <span>
                    Válido hasta las {{ \Illuminate\Support\Carbon::parse($expira_en)->format('H:i:s') }}. Es de un solo
                    uso; si vence, vuelve a generarlo desde Mis préstamos.
                </span>
            </div>
            <a href="{{ route('prestamos.mis') }}" class="boton boton--secundario alinear-inicio">
                <span class="icono" aria-hidden="true">arrow_back</span>Volver
            </a>
        </div>
    </div>
@endsection
