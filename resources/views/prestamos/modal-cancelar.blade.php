{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         modal-cancelar.blade.php
 * @path         resources/views/prestamos/modal-cancelar.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Modal de cancelación con justificación obligatoria: el botón
 *               Confirmar se habilita al alcanzar el mínimo de caracteres.
 *               Variables: prestamo, minimo, operador (true si cancela un
 *               administrador del sistema; false si es el propietario).
 *
 * @requisitos   RF-028, RF-029
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Cancelar reserva);
 *               RED-01 H-05 y H-20; DT-028
 * ============================================================================
--}}
@php
    $idModal = 'modal-cancelar-' . $prestamo->id_prestamo;
    $conError = $errors->cancelacion->has('motivo_cancelacion')
        && (int) old('prestamo_cancelado') === $prestamo->id_prestamo;
@endphp
<dialog id="{{ $idModal }}" class="modal" aria-labelledby="{{ $idModal }}-titulo"
        @if ($conError) data-abrir-al-cargar @endif>
    <form method="POST" action="{{ route('prestamos.cancelar', $prestamo) }}" class="modal__contenido">
        @csrf
        <input type="hidden" name="prestamo_cancelado" value="{{ $prestamo->id_prestamo }}">
        <div class="modal__cabecera">
            <span class="icono-tile icono-tile--44 icono-tile--rojo-fuerte"
                  aria-hidden="true"><span class="icono">priority_high</span></span>
            <div class="pila--4 titulos crece">
                <h2 id="{{ $idModal }}-titulo" class="modal__titulo">
                    {{ $operador ? 'Cancelar reserva' : 'Cancelar solicitud' }}
                </h2>
                <p class="modal__descripcion">
                    Se cancelará <strong>{{ $prestamo->obtenerFolio() }}</strong>
                    @if ($operador) de {{ $prestamo->usuario->nombre_completo }} @endif
                    y los recursos se liberarán. Esta acción no se puede deshacer.
                </p>
            </div>
            <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="campo">
            <label for="{{ $idModal }}-motivo" class="campo__etiqueta">Justificación (obligatoria)</label>
            <textarea id="{{ $idModal }}-motivo" name="motivo_cancelacion" class="control" maxlength="1000" required
                      minlength="{{ $minimo }}" data-minimo="{{ $minimo }}" aria-describedby="{{ $idModal }}-ayuda"
                      placeholder="{{ $operador ? 'Ej. La sala B2 queda fuera de servicio por mantenimiento eléctrico'
                          : 'Ej. Ya no necesito el equipo' }}"
                      @if ($conError) aria-invalid="true"
                      @endif>{{ $conError ? old('motivo_cancelacion') : '' }}</textarea>
            <span id="{{ $idModal }}-ayuda" class="campo__ayuda">
                <span data-contador>0</span> / {{ $minimo }} caracteres mínimos · queda registrada en auditoría
            </span>
            @if ($conError)
                <span class="campo__error">
                    <span class="icono"
                          aria-hidden="true">error</span>{{ $errors->cancelacion->first('motivo_cancelacion') }}
                </span>
            @endif
        </div>
        <div class="acciones acciones--fin">
            <button type="button" class="boton boton--secundario boton--grande" data-cerrar-modal>Cerrar</button>
            <button type="submit" class="boton boton--peligro-solido boton--grande"
                    data-requiere-minimo>Confirmar</button>
        </div>
    </form>
</dialog>
