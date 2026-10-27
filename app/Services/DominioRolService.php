<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         DominioRolService.php
 * @path         app/Services/DominioRolService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Reglas dominio → rol de cada institución (RF-017): alta, baja
 *               y resolución del rol de un correo. Gana la regla del dominio
 *               más específico (subdominio sobre dominio).
 *
 * @requisitos   RF-016, RF-017, RN-001, RS-006
 *
 * @see          DT-034; CU-016
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\DominioRol;
use App\Models\Institucion;
use App\Support\DominioCorreo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Mapeo de roles por dominio.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class DominioRolService
{
    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
    ) {}

    /**
     * Registra una regla; el dominio debe ser el contratado o un subdominio de él.
     *
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  string  $dominio  Dominio o subdominio.
     * @param  Rol  $rol  Alumno o docente.
     * @return DominioRol
     *
     * @throws ReglaNegocioException Si el dominio no pertenece a la institución, ya tiene regla o el rol no aplica.
     */
    public function registrar(Institucion $institucion, string $dominio, Rol $rol): DominioRol
    {
        $dominio = mb_strtolower(trim($dominio));

        if (! $rol->esUsuarioFinal()) {
            throw new ReglaNegocioException('Por dominio solo se asignan los roles de alumno o docente.');
        }

        if (! DominioCorreo::esDeInstitucion($dominio, $institucion->dominio_correo)) {
            throw new ReglaNegocioException(
                'El dominio debe ser ' . $institucion->dominio_correo . ' o un subdominio de él.',
            );
        }

        if (DominioRol::query()->where('dominio', $dominio)->exists()) {
            throw new ReglaNegocioException('Ese dominio ya tiene una regla.');
        }

        return DB::transaction(function () use ($institucion, $dominio, $rol): DominioRol {
            $regla = DominioRol::query()->create([
                'id_institucion' => $institucion->id_institucion,
                'dominio' => $dominio,
                'rol' => $rol,
            ]);
            $this->auditoriaService->registrar('CREAR', $regla, null, $this->auditoriaService->capturar($regla));

            return $regla;
        });
    }

    /**
     * Elimina una regla; las cuentas ya creadas conservan su rol (asignación registrada, RN-001).
     *
     * @param  DominioRol  $regla  Regla de la institución.
     * @return void
     */
    public function eliminar(DominioRol $regla): void
    {
        DB::transaction(function () use ($regla): void {
            $anterior = $this->auditoriaService->capturar($regla);
            $regla->delete();
            $this->auditoriaService->registrar('ELIMINAR', $regla, $anterior, null);
        });
    }

    /**
     * Rol que corresponde al correo según las reglas; null si ninguna aplica.
     *
     * @param  string  $correo  Correo institucional.
     * @param  Collection<int, DominioRol>|null  $reglas  Reglas precargadas (importaciones); null las consulta.
     * @return Rol|null
     */
    public function resolverRol(string $correo, ?Collection $reglas = null): ?Rol
    {
        $dominio = DominioCorreo::extraer($correo);
        $reglas ??= DominioRol::query()->get();

        // El dominio más largo es el más específico: alumnos.escuela.mx gana sobre escuela.mx.
        $aplicables = $reglas
            ->filter(fn (DominioRol $regla): bool => DominioCorreo::esDeInstitucion($dominio, $regla->dominio))
            ->sortByDesc(fn (DominioRol $regla): int => mb_strlen($regla->dominio));

        return $aplicables->first()?->rol;
    }
}
