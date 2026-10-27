{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.6: Penalizaciones y Sanciones Automatizadas
 * @file         modal-exentar.blade.php
 * @path         resources/views/sanciones/modal-exentar.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Diálogo de exención con justificación obligatoria (mínimo 20
 *               caracteres); se reabre si el servidor rechaza el envío.
 *               Variables: penalizacion.
 *
 * @requisitos   RF-044
 *
 * @see          CU-020
 * ============================================================================
--}}
@php
    $idModal = 'modal-exentar-' . $penalizacion->id_penalizacion;
    $conError = $errors->exencion->has('motivo_exencion')
        && (int) old('penalizacion_exentada') === $penalizacion->id_penalizacion;
@endphp
<dialog id="{{ $idModal }}" class="modal" aria-labelledby="{{ $idModal }}-titulo"
        @if ($conError) data-abrir-al-cargar @endif>
    <form method="POST" action="{{ route('sanciones.exentar', $penalizacion) }}" class="modal__contenido">
        @csrf
        <input type="hidden" name="penalizacion_exentada" value="{{ $penalizacion->id_penalizacion }}">
        <div class="modal__cabecera">
            <span class="icono-tile icono-tile--44 icono-tile--ambar" aria-hidden="true">
                <span class="icono">gavel</span>
            </span>
            <div class="pila--4 titulos crece">
                <h2 id="{{ $idModal }}-titulo" class="modal__titulo">Exentar sanción</h2>
                <p class="modal__descripcion">
                    Se restarán {{ $penalizacion->marcas_aplicadas }} marca(s) a
                    <strong>{{ $penalizacion->usuario->nombre_completo }}</strong>; si queda por debajo del umbral se
                    levanta su bloqueo. Queda registrado en auditoría.
                </p>
            </div>
            <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="campo">
            <label for="{{ $idModal }}-motivo" class="campo__etiqueta">Justificación (obligatoria)</label>
            <textarea id="{{ $idModal }}-motivo" name="motivo_exencion" class="control" maxlength="1000" required
                      minlength="20" data-minimo="20" aria-describedby="{{ $idModal }}-ayuda"
                      placeholder="Ej. El retraso se debió a un cierre del edificio autorizado por la dirección"
                      @if ($conError) aria-invalid="true"
                      @endif>{{ $conError ? old('motivo_exencion') : '' }}</textarea>
            <span id="{{ $idModal }}-ayuda" class="campo__ayuda">
                <span data-contador>0</span> / 20 caracteres mínimos
            </span>
            @if ($conError)
                <span class="campo__error">
                    <span class="icono" aria-hidden="true">error</span>{{ $errors->exencion->first('motivo_exencion') }}
                </span>
            @endif
        </div>
        <div class="acciones acciones--fin">
            <button type="button" class="boton boton--secundario boton--grande" data-cerrar-modal>Cerrar</button>
            <button type="submit" class="boton boton--primario boton--grande" data-requiere-minimo>Exentar</button>
        </div>
    </form>
</dialog>
