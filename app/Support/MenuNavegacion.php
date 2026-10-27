<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         MenuNavegacion.php
 * @path         app/Support/MenuNavegacion.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Define el menú lateral de cada rol (máximo 7 elementos, MP
 *               Tabla 65), el destino del buscador global y el texto de la
 *               ruta de navegación. Solo incluye pantallas con funcionalidad
 *               real (DT-028).
 *
 * @requisitos   RNF-008, RS-006
 *
 * @see          Prototipo interactivo SPEI/design_handoff_spei_web/README.md (Menú por rol)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

use App\Enums\EstadoPrestamo;
use App\Enums\Rol;
use App\Models\Usuario;
use App\Services\ConsultaPrestamoService;

/**
 * Menú lateral y buscador según el rol del usuario.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class MenuNavegacion
{
    /**
     * @param  ConsultaPrestamoService  $consultaPrestamoService  Préstamos visibles por usuario.
     */
    public function __construct(
        private ConsultaPrestamoService $consultaPrestamoService,
    ) {}

    /**
     * Secciones del menú con sus enlaces.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return list<array{titulo: string, enlaces: list<array{ruta: string, icono: string, texto: string,
     *     patron: list<string>, contador: int|null}>}>
     */
    public function obtenerSecciones(Usuario $usuario): array
    {
        return match ($usuario->rol) {
            Rol::Superadmin => $this->armarSuperadmin(),
            Rol::AdminInstitucional => $this->armarAdminInstitucional(),
            Rol::AdminSistema => $this->armarAdminSistema($usuario),
            Rol::Asistente => $this->armarAsistente(),
            Rol::Alumno, Rol::Docente => $this->armarUsuarioFinal($usuario),
        };
    }

    /**
     * Ruta y texto de ayuda del buscador global del topbar.
     *
     * @param  Usuario  $usuario  Usuario autenticado.
     * @return array{ruta: string, marcador: string}
     */
    public function obtenerBuscador(Usuario $usuario): array
    {
        return match ($usuario->rol) {
            Rol::Superadmin => ['ruta' => 'instituciones.index', 'marcador' => 'Buscar institución o dominio'],
            Rol::AdminInstitucional => ['ruta' => 'auditoria.index', 'marcador' => 'Buscar en auditoría'],
            Rol::AdminSistema, Rol::Asistente => [
                'ruta' => 'prestamos.index',
                'marcador' => 'Buscar folio o solicitante',
            ],
            Rol::Alumno, Rol::Docente => ['ruta' => 'catalogo.index', 'marcador' => 'Buscar recurso o marca'],
        };
    }

    /**
     * Menú del superadmin de la plataforma.
     *
     * @return list<array<string, mixed>>
     */
    private function armarSuperadmin(): array
    {
        return [[
            'titulo' => 'Plataforma',
            'enlaces' => [
                $this->enlace('panel', 'space_dashboard', 'Panel', ['panel']),
                $this->enlace('instituciones.index', 'domain', 'Instituciones', ['instituciones.*']),
                $this->enlace('planes.index', 'sell', 'Planes', ['planes.*']),
            ],
        ]];
    }

    /**
     * Menú del administrador institucional.
     *
     * @return list<array<string, mixed>>
     */
    private function armarAdminInstitucional(): array
    {
        return [[
            'titulo' => 'Institución',
            'enlaces' => [
                $this->enlace('suscripcion.show', 'workspace_premium', 'Suscripción', ['suscripcion.*', 'panel']),
                $this->enlace('administradores.index', 'manage_accounts', 'Administradores', ['administradores.*']),
                $this->enlace('auditoria.index', 'manage_search', 'Auditoría', ['auditoria.*']),
            ],
        ]];
    }

    /**
     * Menú del administrador del sistema; el responsable de un área no gestiona áreas.
     *
     * @param  Usuario  $usuario  Administrador del sistema.
     * @return list<array<string, mixed>>
     */
    private function armarAdminSistema(Usuario $usuario): array
    {
        $administracion = [];

        if (! $usuario->esResponsableDeArea()) {
            $administracion[] = $this->enlace('areas.index', 'apartment', 'Áreas', ['areas.*']);
        }

        // RF-016 a RF-021: el responsable de un área solo gestiona a los asistentes de su área.
        $administracion[] = $this->enlace(
            'usuarios.index',
            'group',
            $usuario->esResponsableDeArea() ? 'Asistentes' : 'Usuarios',
            ['usuarios.*', 'dominios.*'],
        );
        $administracion[] = $this->enlace('sanciones.index', 'gavel', 'Sanciones', ['sanciones.*']);
        $administracion[] = $this->enlace('reportes.index', 'bar_chart', 'Reportes', ['reportes.*']);

        // La bitácora completa del tenant no se expone al responsable de una sola área.
        if (! $usuario->esResponsableDeArea()) {
            $administracion[] = $this->enlace('auditoria.index', 'manage_search', 'Auditoría', ['auditoria.*']);
        }

        return [
            [
                'titulo' => 'Operación',
                'enlaces' => [
                    $this->enlace('panel', 'space_dashboard', 'Dashboard', ['panel']),
                    $this->enlace(
                        'prestamos.index',
                        'assignment',
                        'Préstamos',
                        ['prestamos.*'],
                        $this->contarPendientes($usuario),
                    ),
                    $this->enlace('mostrador.index', 'qr_code_scanner', 'Mostrador', ['mostrador.*']),
                    $this->enlace('recursos.index', 'inventory_2', 'Recursos', ['recursos.*']),
                ],
            ],
            ['titulo' => 'Administración', 'enlaces' => $administracion],
        ];
    }

    /**
     * Menú del asistente de mostrador.
     *
     * @return list<array<string, mixed>>
     */
    private function armarAsistente(): array
    {
        return [[
            'titulo' => 'Mostrador',
            'enlaces' => [
                $this->enlace(
                    'mostrador.index',
                    'qr_code_scanner',
                    'Entregas y devoluciones',
                    ['mostrador.*', 'panel'],
                ),
                $this->enlace('prestamos.index', 'assignment', 'Préstamos', ['prestamos.*']),
            ],
        ]];
    }

    /**
     * Menú de alumnos y docentes.
     *
     * @param  Usuario  $usuario  Usuario final.
     * @return list<array<string, mixed>>
     */
    private function armarUsuarioFinal(Usuario $usuario): array
    {
        $abiertos = $this->consultaPrestamoService->paraUsuario($usuario)
            ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
            ->count();

        return [[
            'titulo' => 'Préstamos',
            'enlaces' => [
                $this->enlace('catalogo.index', 'inventory_2', 'Catálogo', ['catalogo.*']),
                $this->enlace('prestamos.create', 'add_circle', 'Nueva solicitud', ['prestamos.create']),
                $this->enlace(
                    'prestamos.mis',
                    'assignment',
                    'Mis préstamos',
                    ['prestamos.mis', 'prestamos.show'],
                    $abiertos,
                ),
            ],
        ]];
    }

    /**
     * Préstamos por recoger dentro del ámbito del operador (contador del menú, RED-01 H-07).
     *
     * @param  Usuario  $usuario  Operador autenticado.
     * @return int
     */
    private function contarPendientes(Usuario $usuario): int
    {
        return $this->consultaPrestamoService->paraUsuario($usuario)
            ->where('estado', EstadoPrestamo::Pendiente->value)
            ->count();
    }

    /**
     * Arma un enlace del menú.
     *
     * @param  string  $ruta  Nombre de la ruta.
     * @param  string  $icono  Nombre del icono de Material Symbols.
     * @param  string  $texto  Texto visible.
     * @param  list<string>  $patron  Patrones de ruta que lo marcan como activo.
     * @param  int|null  $contador  Número en la píldora; null o cero lo oculta.
     * @return array{ruta: string, icono: string, texto: string, patron: list<string>, contador: int|null}
     */
    private function enlace(string $ruta, string $icono, string $texto, array $patron, ?int $contador = null): array
    {
        return [
            'ruta' => $ruta,
            'icono' => $icono,
            'texto' => $texto,
            'patron' => $patron,
            'contador' => $contador === 0 ? null : $contador,
        ];
    }
}
