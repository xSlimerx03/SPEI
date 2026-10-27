{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         modal-no-devuelto.blade.php
 * @path         resources/views/prestamos/modal-no-devuelto.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Diálogo para cerrar un préstamo atrasado cuyo recurso no se
 *               devolvió (RF-042): justificación obligatoria; los recursos
 *               quedan fuera de servicio y se aplica la sanción de la matriz.
 *               Variables: prestamo.
 *
 * @requisitos   RF-039, RF-042
 *
 * @see          CU-019
 * ============================================================================
--}}
@php
    $idModal = 'modal-no-devuelto-' . $prestamo->id_prestamo;
    $conError = $errors->noDevuelto->has('motivo');
@endphp
<dialog id="{{ $idModal }}" class="modal" aria-labelledby="{{ $idModal }}-titulo"
        @if ($conError) data-abrir-al-cargar @endif>
    <form method="POST" action="{{ route('prestamos.no-devuelto', $prestamo) }}" class="modal__contenido">
        @csrf
        <div class="modal__cabecera">
            <span class="icono-tile icono-tile--44 icono-tile--rojo-fuerte" aria-hidden="true">
                <span class="icono">report</span>
            </span>
            <div class="pila--4 titulos crece">
                <h2 id="{{ $idModal }}-titulo" class="modal__titulo">Registrar no devuelto</h2>
                <p class="modal__descripcion">
                    Se cerrará <strong>{{ $prestamo->obtenerFolio() }}</strong>, los recursos pasarán a mantenimiento y
                    se aplicará la sanción de la matriz a {{ $prestamo->usuario->nombre_completo }}. No se puede
                    deshacer.
                </p>
            </div>
            <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                <span class="icono" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="campo">
            <label for="{{ $idModal }}-motivo" class="campo__etiqueta">Justificación (obligatoria)</label>
            <textarea id="{{ $idModal }}-motivo" name="motivo" class="control" maxlength="1000" required
                      minlength="20" data-minimo="20" aria-describedby="{{ $idModal }}-ayuda"
                      placeholder="Ej. Se contactó al alumno dos veces y no ha regresado el proyector"
                      @if ($conError) aria-invalid="true" @endif>{{ $conError ? old('motivo') : '' }}</textarea>
            <span id="{{ $idModal }}-ayuda" class="campo__ayuda">
                <span data-contador>0</span> / 20 caracteres mínimos · queda registrada en auditoría
            </span>
            @if ($conError)
                <span class="campo__error">
                    <span class="icono" aria-hidden="true">error</span>{{ $errors->noDevuelto->first('motivo') }}
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
