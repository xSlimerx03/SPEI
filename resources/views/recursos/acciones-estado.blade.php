{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         acciones-estado.blade.php
 * @path         resources/views/recursos/acciones-estado.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Botones "Mantenimiento" / "Reactivar" y "Desactivar" /
 *               "Activar" de un recurso. Solo se ofrecen las transiciones
 *               manuales permitidas (CU-009 E2). Variable: recurso.
 *
 * @requisitos   RF-013, RF-014
 *
 * @see          docs/requisitos/BBB_CU009_CambiarEstadoRecurso_v1.0.md
 * ============================================================================
--}}
@if (! $recurso->estado->estaEnUso())
    @php
        $enMantenimiento = $recurso->estado === \App\Enums\EstadoRecurso::Mantenimiento;
        $destino = $enMantenimiento ? \App\Enums\EstadoRecurso::Disponible : \App\Enums\EstadoRecurso::Mantenimiento;
    @endphp
    <form method="POST" action="{{ route('recursos.estado', $recurso) }}" class="formulario-en-linea">
        @csrf
        @method('PATCH')
        <input type="hidden" name="estado" value="{{ $destino->value }}">
        <button type="submit" class="boton boton--pequeno boton--secundario">
            <span class="icono icono--boton" aria-hidden="true">{{ $enMantenimiento ? 'check' : 'build' }}</span>
            {{ $enMantenimiento ? 'Reactivar' : 'Enviar mantenimiento' }}
        </button>
    </form>
    <form method="POST" action="{{ route('recursos.activo', $recurso) }}" class="formulario-en-linea">
        @csrf
        @method('PATCH')
        <input type="hidden" name="activo" value="{{ $recurso->activo ? 0 : 1 }}">
        <button type="submit" class="boton boton--pequeno boton--secundario">
            {{ $recurso->activo ? 'Desactivar' : 'Activar' }}
        </button>
    </form>
@endif
