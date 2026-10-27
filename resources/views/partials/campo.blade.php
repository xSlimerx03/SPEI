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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Campo de texto con etiqueta, ícono opcional, ayuda y error
 *               asociados por aria-describedby; las contraseñas incluyen el
 *               botón mostrar/ocultar. Variables: nombre, etiqueta, tipo,
 *               valor, ayuda, requerido, atributos, icono, marcador.
 *
 * @requisitos   RNF-008
 *
 * @see          Manual de Programación v1.2, Tablas 66, 67 y 69; DT-028
 * ============================================================================
--}}
@php
    $tipo ??= 'text';
    $valor ??= null;
    $ayuda ??= null;
    $requerido ??= true;
    $atributos ??= '';
    $icono ??= null;
    $marcador ??= null;
    $idAyuda = $nombre . '-ayuda';
    $idError = $nombre . '-error';
    $valorMostrado = $tipo === 'password' ? null : old($nombre, $valor);
    $describe = trim(($ayuda ? $idAyuda : '') . ' ' . ($errors->has($nombre) ? $idError : ''));
    $conAccion = $tipo === 'password';
@endphp
<div class="campo">
    <label for="{{ $nombre }}" class="campo__etiqueta">{{ $etiqueta }}</label>
    <div class="control-icono {{ $icono ? 'control-icono--con-icono' : '' }} {{ $conAccion ? 'control-icono--accion'
        : '' }}">
        @if ($icono)
            <span class="icono" aria-hidden="true">{{ $icono }}</span>
        @endif
        <input type="{{ $tipo }}" id="{{ $nombre }}" name="{{ $nombre }}" class="control"
               value="{{ $valorMostrado }}" @if ($marcador) placeholder="{{ $marcador }}" @endif
               @if ($requerido) required @endif
               @if ($describe !== '') aria-describedby="{{ $describe }}" @endif
               @error($nombre) aria-invalid="true" @enderror
               {!! $atributos !!}>
        @if ($conAccion)
            <button type="button" class="control-icono__accion" data-mostrar-contrasena="{{ $nombre }}"
                    aria-label="Mostrar contraseña" aria-pressed="false" hidden>
                <span class="icono icono--20" aria-hidden="true">visibility</span>
            </button>
        @endif
    </div>
    @if ($ayuda)
        <span id="{{ $idAyuda }}" class="campo__ayuda">{{ $ayuda }}</span>
    @endif
    @error($nombre)
        <span id="{{ $idError }}" class="campo__error">
            <span class="icono" aria-hidden="true">error</span>{{ $message }}
        </span>
    @enderror
</div>
