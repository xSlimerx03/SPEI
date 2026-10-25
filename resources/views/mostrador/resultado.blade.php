{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         resultado.blade.php
 * @path         resources/views/mostrador/resultado.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Resultado de un código válido: solicitante, recursos,
 *               condición (Bueno/Regular/Malo y Dañado en devolución),
 *               incidencia y confirmación. Variables: prestamo, token, codigo.
 *
 * @requisitos   RF-034, RF-035, RF-036, RF-037
 *
 * @see          app/Http/Controllers/Web/PrestamoController.php (confirmar); DT-028
 * ============================================================================
--}}
@php
    $esEntrega = $token->tipo_token === 'entrega';
    $condiciones = $esEntrega
        ? ['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo']
        : ['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo', 'danado' => 'Dañado'];
    $solicitante = $prestamo->usuario;
@endphp
<section class="resultado-escaneo" aria-labelledby="titulo-resultado">
    <div class="resultado-escaneo__cabecera">
        <span class="icono" aria-hidden="true">verified</span>
        <div class="titulos crece">
            <h2 id="titulo-resultado" class="negrita texto-15">Código válido · {{ $esEntrega ? 'Entrega' : 'Devolución' }}</h2>
            <span class="texto-13">{{ $prestamo->obtenerFolio() }} · vence a las {{ $token->fecha_expiracion->format('H:i:s') }}</span>
        </div>
    </div>
    <form method="POST" action="{{ route('prestamos.confirmar', $prestamo) }}" class="pila--16 titulos relleno-20">
        @csrf
        <input type="hidden" name="codigo" value="{{ $codigo }}">
        <div class="en-linea">
            <span class="avatar avatar--48 avatar--azul" aria-hidden="true">{{ \App\Support\Iniciales::de($solicitante->nombre_completo) }}</span>
            <div class="titulos crece">
                <span class="negrita texto-16">{{ $solicitante->nombre_completo }}</span>
                <span class="texto-13 texto-sec">
                    {{ $solicitante->rol->obtenerEtiqueta() }} ·
                    {{ \App\Support\FormatoFecha::periodo($prestamo->fecha_hora_inicio, $prestamo->fecha_hora_fin) }}
                </span>
            </div>
        </div>

        <fieldset class="grupo-campos">
            <legend class="texto-12 negrita texto-sec">Condición al {{ $esEntrega ? 'entregar' : 'recibir' }}</legend>
            <div class="condicion alinear-inicio" role="radiogroup">
                @foreach ($condiciones as $valor => $texto)
                    <label>
                        <input type="radio" name="condicion" value="{{ $valor }}" @checked(old('condicion', 'bueno') === $valor)>
                        <span>{{ $texto }}</span>
                    </label>
                @endforeach
            </div>
            @foreach ($prestamo->detalles as $detalle)
                <div class="recurso-condicion">
                    <div class="titulos crece">
                        <span class="negrita">{{ $detalle->recurso->nombre_recurso }}</span>
                        <span class="mono texto-sec texto-11">{{ $detalle->recurso->numero_inventario }}</span>
                    </div>
                    @unless ($esEntrega)
                        <span class="texto-12 texto-sec">Entregado: {{ ucfirst($detalle->condicion_entrega) }}</span>
                    @endunless
                </div>
            @endforeach
        </fieldset>

        <div class="campo">
            <label for="incidencia" class="campo__etiqueta en-linea en-linea--8 texto-error">
                <span class="icono icono--18" aria-hidden="true">report</span>Registrar incidencia (opcional)
            </label>
            <textarea id="incidencia" name="incidencia" class="control control--96" maxlength="2000"
                      placeholder="Obligatoria si la condición es mala o dañada">{{ old('incidencia') }}</textarea>
        </div>

        <div class="acciones">
            <a href="{{ route('mostrador.index') }}" class="boton boton--secundario boton--grande boton--crece">Cancelar</a>
            <button type="submit" class="boton boton--primario boton--grande boton--crece-2">
                <span class="icono" aria-hidden="true">check</span>Confirmar {{ $esEntrega ? 'entrega' : 'devolución' }}
            </button>
        </div>
    </form>
</section>
