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
 * @requisitos   RF-008
 *
 * @see          DT-028
 * ============================================================================
--}}
<div class="fila-lista fila-lista--12">
    <span class="avatar" aria-hidden="true">{{ \App\Support\Iniciales::de($administrador->nombre_completo) }}</span>
    <div class="titulos crece-220">
        <span class="negrita">{{ $administrador->nombre_completo }}</span>
        <span class="texto-12 texto-sec truncar">{{ $administrador->correo_institucional }}</span>
    </div>
    <span class="texto-apoyo celda-200">{{ $administrador->areaAsignada?->nombre_area ?? 'Todas las áreas' }}</span>
    <span class="insignia {{ $administrador->activo ? 'insignia--exito' : 'insignia--pendiente' }}">
        {{ $administrador->activo ? 'Registro completo' : 'Invitación pendiente' }}
    </span>
    @unless ($administrador->activo)
        <form method="POST" action="{{ route('administradores.reenviar', $administrador) }}" class="formulario-en-linea">
            @csrf
            <button type="submit" class="boton boton--secundario boton--pequeno">
                <span class="icono" aria-hidden="true">send</span>Reenviar
            </button>
        </form>
    @endunless
</div>
