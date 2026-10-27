{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         fila.blade.php
 * @path         resources/views/administradores/fila.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fila de un administrador del sistema: avatar, nombre, correo,
 *               área a cargo, estado del registro y reenvío de invitación.
 *               Variable: administrador.
 *
 * @requisitos   RF-008, RF-019
 *
 * @see          DT-028
 * ============================================================================
--}}
@php
    $estadoCuenta = $administrador->obtenerEstadoCuenta();
    [$textoEstado, $claseEstado] = match ($estadoCuenta) {
        'pendiente' => ['Invitación pendiente', 'insignia--pendiente'],
        'activa' => ['Activa', 'insignia--exito'],
        default => ['Dada de baja', 'insignia--neutra'],
    };
@endphp
<div class="fila-lista fila-lista--12">
    <span class="avatar" aria-hidden="true">{{ \App\Support\Iniciales::de($administrador->nombre_completo) }}</span>
    <div class="titulos crece-220">
        <span class="negrita">{{ $administrador->nombre_completo }}</span>
        <span class="texto-12 texto-sec truncar">{{ $administrador->correo_institucional }}</span>
    </div>
    <span class="texto-apoyo celda-200">{{ $administrador->areaAsignada?->nombre_area ?? 'Todas las áreas' }}</span>
    <span class="insignia {{ $claseEstado }}">{{ $textoEstado }}</span>
    <div class="acciones">
        <a href="{{ route('administradores.edit', $administrador) }}" class="boton boton--fantasma boton--pequeno"
           aria-label="Cambiar el área de {{ $administrador->nombre_completo }}">
            <span class="icono" aria-hidden="true">apartment</span>Cambiar área
        </a>
        @if ($estadoCuenta === 'pendiente')
            <form method="POST" action="{{ route('administradores.reenviar', $administrador) }}"
                  class="formulario-en-linea">
                @csrf
                <button type="submit" class="boton boton--secundario boton--pequeno">
                    <span class="icono" aria-hidden="true">send</span>Reenviar
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('administradores.activo', $administrador) }}"
                  class="formulario-en-linea"
                  @if ($administrador->activo) data-confirmar="¿Dar de baja este administrador?" @endif>
                @csrf
                @method('PATCH')
                <input type="hidden" name="activo" value="{{ $administrador->activo ? 0 : 1 }}">
                <button type="submit" class="boton boton--fantasma boton--pequeno">
                    <span class="icono"
                          aria-hidden="true">{{ $administrador->activo ? 'person_off' : 'person_check' }}</span>
                    {{ $administrador->activo ? 'Dar baja' : 'Reactivar' }}
                </button>
            </form>
        @endif
    </div>
</div>
