{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         fila.blade.php
 * @path         resources/views/usuarios/fila.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Fila de un usuario: rol, área, estado de la cuenta y acciones
 *               (editar, reenviar invitación, dar de baja o reactivar).
 *               Variables: usuario.
 *
 * @requisitos   RF-016, RF-018, RF-020, RF-021
 *
 * @see          CU-016, CU-017
 * ============================================================================
--}}
@php
    $estadoCuenta = $usuario->obtenerEstadoCuenta();
    [$textoEstado, $claseEstado] = match ($estadoCuenta) {
        'pendiente' => ['Invitación pendiente', 'insignia--pendiente'],
        'activa' => ['Activa', 'insignia--exito'],
        default => ['Dada de baja', 'insignia--neutra'],
    };
@endphp
<div class="fila-lista fila-lista--12">
    <span class="avatar" aria-hidden="true">{{ \App\Support\Iniciales::de($usuario->nombre_completo) }}</span>
    <div class="titulos crece-220">
        <span class="negrita">{{ $usuario->nombre_completo }}</span>
        <span class="texto-12 texto-sec truncar">{{ $usuario->correo_institucional }}</span>
    </div>
    <span class="insignia insignia--suave">{{ $usuario->rol->obtenerEtiqueta() }}</span>
    <span class="texto-apoyo celda-200">{{ $usuario->areaAsignada?->nombre_area ?? '—' }}</span>
    <span class="insignia {{ $claseEstado }}">{{ $textoEstado }}</span>
    <div class="acciones">
        <a href="{{ route('usuarios.edit', $usuario) }}" class="boton boton--fantasma boton--pequeno"
           aria-label="Editar a {{ $usuario->nombre_completo }}">
            <span class="icono" aria-hidden="true">edit</span>Editar
        </a>
        @if ($estadoCuenta === 'pendiente')
            <form method="POST" action="{{ route('usuarios.reenviar', $usuario) }}" class="formulario-en-linea">
                @csrf
                <button type="submit" class="boton boton--secundario boton--pequeno">
                    <span class="icono" aria-hidden="true">send</span>Reenviar
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('usuarios.activo', $usuario) }}" class="formulario-en-linea"
                  @if ($usuario->activo) data-confirmar="¿Dar de baja esta cuenta? No podrá iniciar sesión." @endif>
                @csrf
                @method('PATCH')
                <input type="hidden" name="activo" value="{{ $usuario->activo ? 0 : 1 }}">
                <button type="submit" class="boton boton--fantasma boton--pequeno">
                    <span class="icono" aria-hidden="true">{{ $usuario->activo ? 'person_off' : 'person_check' }}</span>
                    {{ $usuario->activo ? 'Dar baja' : 'Reactivar' }}
                </button>
            </form>
        @endif
    </div>
</div>
