{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         campo.blade.php
 * @path         resources/views/partials/campo.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Campo de texto con etiqueta, ayuda y error asociados por
 *               aria-describedby. Variables: nombre, etiqueta, tipo, valor,
 *               ayuda, requerido, atributos.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, Tablas 66, 67 y 69
 * ============================================================================
--}}
@php
    $tipo ??= 'text';
    $valor ??= null;
    $ayuda ??= null;
    $requerido ??= true;
    $atributos ??= '';
    $idAyuda = $nombre . '-ayuda';
    $idError = $nombre . '-error';
    $valorMostrado = $tipo === 'password' ? null : old($nombre, $valor);
@endphp
<div class="campo">
    <label for="{{ $nombre }}" class="campo__etiqueta">{{ $etiqueta }}</label>
    <input type="{{ $tipo }}" id="{{ $nombre }}" name="{{ $nombre }}" class="control" value="{{ $valorMostrado }}"
           @if ($requerido) required @endif
           @if ($ayuda || $errors->has($nombre)) aria-describedby="{{ $ayuda ? $idAyuda : '' }} {{ $errors->has($nombre) ? $idError : '' }}" @endif
           @error($nombre) aria-invalid="true" @enderror
           {!! $atributos !!}>
    @if ($ayuda)
        <span id="{{ $idAyuda }}" class="campo__ayuda">{{ $ayuda }}</span>
    @endif
    @error($nombre)
        <span id="{{ $idError }}" class="campo__error">
            <span class="icono icono--boton" aria-hidden="true">error</span>{{ $message }}
        </span>
    @enderror
</div>
