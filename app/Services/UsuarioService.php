<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         UsuarioService.php
 * @path         app/Services/UsuarioService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Alta por invitación de alumnos, docentes y asistentes; carga
 *               masiva por CSV (todo o nada); cambio de rol y de área con
 *               delegación al asistente registrada en auditoría; baja y
 *               reactivación. El administrador del sistema sin área gestiona
 *               a todos; el responsable de un área solo a sus asistentes.
 *
 * @requisitos   RF-016, RF-017, RF-018, RF-020, RF-021, RN-001, RS-006
 *
 * @see          CU-016, CU-017, CU-018; DT-034, DT-035
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPrestamo;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Area;
use App\Models\DominioRol;
use App\Models\Institucion;
use App\Models\Prestamo;
use App\Models\Usuario;
use App\Support\DominioCorreo;
use App\Support\LectorCsv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Gestión de usuarios finales y asistentes.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class UsuarioService
{
    /**
     * Encabezado obligatorio del CSV de usuarios, en este orden.
     */
    public const ENCABEZADO = ['correo_institucional', 'nombre_completo', 'rol', 'nombre_area'];

    /**
     * Roles que se dan de alta desde este módulo; los administradores siguen en CU-004.
     */
    public const ROLES_GESTIONABLES = [Rol::Alumno, Rol::Docente, Rol::Asistente];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     * @param  InvitacionService  $invitacionService  Dependencia inyectada.
     * @param  DominioRolService  $dominioRolService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
        private InvitacionService $invitacionService,
        private DominioRolService $dominioRolService,
    ) {}

    /**
     * Crea la cuenta inactiva y le envía la invitación.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  array{nombre_completo: string, correo_institucional: string, rol?: string|null,
     *     id_area_asignada?: int|null}  $datos  Datos validados del formulario.
     * @return Usuario
     *
     * @throws ReglaNegocioException Si el correo, el rol o el área no son válidos para el actor.
     */
    public function invitar(Usuario $actor, Institucion $institucion, array $datos): Usuario
    {
        $alta = $this->prepararAlta($actor, $institucion, $datos, DominioRol::query()->get());

        if (Usuario::withoutGlobalScopes()->where('correo_institucional', $alta['correo_institucional'])->exists()) {
            throw new ReglaNegocioException('El correo ya está registrado en el SPEI.');
        }

        $usuario = DB::transaction(function () use ($institucion, $alta): Usuario {
            $usuario = $this->crear($institucion, $alta);
            $this->auditoriaService->registrar('CREAR', $usuario, null, $this->auditoriaService->capturar($usuario));

            return $usuario;
        });

        $this->invitacionService->enviar($usuario, $institucion);

        return $usuario;
    }

    /**
     * Importa el CSV completo o ninguna fila y envía una invitación por cuenta creada.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  UploadedFile  $archivo  CSV validado por tipo y tamaño.
     * @return int Cuentas creadas.
     *
     * @throws ValidationException Si el encabezado o alguna fila no son válidos.
     */
    public function importar(Usuario $actor, Institucion $institucion, UploadedFile $archivo): int
    {
        $filas = LectorCsv::leer($archivo, self::ENCABEZADO);
        [$altas, $errores] = $this->validarFilas($actor, $institucion, $filas);

        if ($errores !== []) {
            throw ValidationException::withMessages(['archivo' => $errores]);
        }

        $usuarios = DB::transaction(function () use ($institucion, $altas): array {
            $usuarios = array_map(fn (array $alta): Usuario => $this->crear($institucion, $alta), $altas);
            $this->auditoriaService->registrarMasivo('IMPORTAR', 'usuarios', $usuarios[0]->id_usuario,
                $institucion->id_institucion, [
                    'total' => count($usuarios),
                    'primer_id' => $usuarios[0]->id_usuario,
                    'ultimo_id' => $usuarios[count($usuarios) - 1]->id_usuario,
                ]);

            return $usuarios;
        });

        foreach ($usuarios as $usuario) {
            $this->invitacionService->enviar($usuario, $institucion);
        }

        return count($usuarios);
    }

    /**
     * Cambia el rol y el área; asignar o mover un asistente se registra como delegación (RF-021).
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Usuario  $usuario  Cuenta gestionada.
     * @param  array{rol: string, id_area_asignada?: int|null}  $datos  Datos validados.
     * @return Usuario
     *
     * @throws ReglaNegocioException Si el actor no gestiona la cuenta o el rol/área no son válidos.
     */
    public function actualizar(Usuario $actor, Usuario $usuario, array $datos): Usuario
    {
        $this->autorizarGestion($actor, $usuario);
        $rol = $this->obtenerRolGestionable($datos['rol']);
        $area = $this->resolverArea($actor, $rol, $datos['id_area_asignada'] ?? null);
        $delegacion = $rol === Rol::Asistente && $area !== $usuario->id_area_asignada;

        return DB::transaction(function () use ($usuario, $rol, $area, $delegacion): Usuario {
            $anterior = $this->auditoriaService->capturar($usuario);
            $usuario->update(['rol' => $rol, 'id_area_asignada' => $area]);
            $this->auditoriaService->registrar(
                $delegacion ? 'DELEGAR' : 'ACTUALIZAR',
                $usuario,
                $anterior,
                $this->auditoriaService->capturar($usuario),
            );

            return $usuario;
        });
    }

    /**
     * Da de baja o reactiva una cuenta registrada; las invitaciones pendientes no se activan aquí.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Usuario  $usuario  Cuenta gestionada.
     * @param  bool  $activo  Estado deseado.
     * @return void
     *
     * @throws ReglaNegocioException Si la cuenta no completó su registro o tiene préstamos abiertos.
     */
    public function cambiarActivo(Usuario $actor, Usuario $usuario, bool $activo): void
    {
        $this->autorizarGestion($actor, $usuario);

        if (! $usuario->completoRegistro()) {
            throw new ReglaNegocioException('La cuenta aún no completa su registro; reenvíe la invitación.');
        }

        if (! $activo && $this->tienePrestamosAbiertos($usuario)) {
            throw new ReglaNegocioException('La cuenta tiene préstamos pendientes o activos; ciérrelos antes.');
        }

        DB::transaction(function () use ($usuario, $activo): void {
            $anterior = $this->auditoriaService->capturar($usuario);
            $usuario->update(['activo' => $activo]);
            $this->auditoriaService->registrar(
                $activo ? 'REACTIVAR' : 'DAR_BAJA',
                $usuario,
                $anterior,
                $this->auditoriaService->capturar($usuario),
            );
        });
    }

    /**
     * Reenvía la invitación de una cuenta gestionada por el actor.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Usuario  $usuario  Cuenta con invitación pendiente.
     * @return void
     *
     * @throws ReglaNegocioException Si el actor no gestiona la cuenta o ya se registró.
     */
    public function reenviarInvitacion(Usuario $actor, Usuario $usuario): void
    {
        $this->autorizarGestion($actor, $usuario);
        $this->invitacionService->reenviar($usuario);
    }

    /**
     * Consulta de las cuentas que el actor puede ver: el responsable de un área solo ve a sus asistentes.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @return Builder<Usuario>
     */
    public function consultarGestionables(Usuario $actor): Builder
    {
        $consulta = Usuario::query()
            ->where('id_institucion', $actor->id_institucion)
            ->whereIn('rol', array_map(fn (Rol $rol): string => $rol->value, self::ROLES_GESTIONABLES));

        if ($actor->esResponsableDeArea()) {
            $consulta->where('rol', Rol::Asistente)->where('id_area_asignada', $actor->id_area_asignada);
        }

        return $consulta;
    }

    /**
     * Rechaza la operación si la cuenta no es gestionable por el actor.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Usuario  $usuario  Cuenta objetivo.
     * @return void
     *
     * @throws ReglaNegocioException
     */
    public function autorizarGestion(Usuario $actor, Usuario $usuario): void
    {
        if (! $this->consultarGestionables($actor)->whereKey($usuario->id_usuario)->exists()) {
            throw new ReglaNegocioException('No puede gestionar esta cuenta.');
        }
    }

    /**
     * Normaliza y valida un alta: dominio, rol (explícito o por dominio, RF-017) y área según el actor.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  array<string, mixed>  $datos  Datos del formulario o de una fila del CSV.
     * @param  Collection<int, DominioRol>  $reglas  Reglas de dominio de la institución.
     * @return array{nombre_completo: string, correo_institucional: string, rol: Rol, id_area_asignada: int|null}
     *
     * @throws ReglaNegocioException
     */
    private function prepararAlta(Usuario $actor, Institucion $institucion, array $datos, Collection $reglas): array
    {
        $correo = mb_strtolower(trim((string) $datos['correo_institucional']));

        if (! DominioCorreo::perteneceA($correo, $institucion->dominio_correo)) {
            throw new ReglaNegocioException('El correo no pertenece al dominio ' . $institucion->dominio_correo . '.');
        }

        $rolIndicado = trim((string) ($datos['rol'] ?? ''));
        $rol = $rolIndicado !== ''
            ? $this->obtenerRolGestionable($rolIndicado)
            : $this->dominioRolService->resolverRol($correo, $reglas);

        if ($rol === null) {
            throw new ReglaNegocioException('Ninguna regla de dominio asigna un rol a este correo; indique el rol.');
        }

        return [
            'nombre_completo' => trim((string) $datos['nombre_completo']),
            'correo_institucional' => $correo,
            'rol' => $rol,
            'id_area_asignada' => $this->resolverArea($actor, $rol, $datos['id_area_asignada'] ?? null),
        ];
    }

    /**
     * Convierte el texto en un rol que este módulo puede asignar.
     *
     * @param  string  $valor  Valor recibido.
     * @return Rol
     *
     * @throws ReglaNegocioException
     */
    private function obtenerRolGestionable(string $valor): Rol
    {
        $rol = Rol::tryFrom(mb_strtolower(trim($valor)));

        if ($rol === null || ! in_array($rol, self::ROLES_GESTIONABLES, true)) {
            throw new ReglaNegocioException('El rol debe ser alumno, docente o asistente.');
        }

        return $rol;
    }

    /**
     * Área de la cuenta: obligatoria y activa para asistentes, nula para usuarios finales. El responsable de
     * un área solo da de alta asistentes de su propia área.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Rol  $rol  Rol de la cuenta.
     * @param  mixed  $idArea  Área indicada.
     * @return int|null
     *
     * @throws ReglaNegocioException
     */
    private function resolverArea(Usuario $actor, Rol $rol, mixed $idArea): ?int
    {
        if ($actor->esResponsableDeArea() && $rol !== Rol::Asistente) {
            throw new ReglaNegocioException('El responsable de un área solo gestiona asistentes.');
        }

        if ($rol !== Rol::Asistente) {
            return null;
        }

        $idArea = $actor->esResponsableDeArea() ? $actor->id_area_asignada : (int) $idArea;

        if (! Area::query()->activas()->whereKey($idArea)->exists()) {
            throw new ReglaNegocioException('Seleccione un área activa para el asistente.');
        }

        return $idArea;
    }

    /**
     * Valida cada fila del CSV y devuelve las altas listas y los errores por línea.
     *
     * @param  Usuario  $actor  Administrador del sistema.
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  array<int, array<string, string>>  $filas  Filas por número de línea.
     * @return array{0: list<array{nombre_completo: string, correo_institucional: string, rol: Rol,
     *     id_area_asignada: int|null}>, 1: list<string>}
     */
    private function validarFilas(Usuario $actor, Institucion $institucion, array $filas): array
    {
        $reglas = DominioRol::query()->get();
        $areas = Area::query()->get(['id_area', 'nombre_area'])
            ->mapWithKeys(fn (Area $area): array => [mb_strtolower($area->nombre_area) => $area->id_area]);
        $existentes = $this->obtenerCorreosExistentes($filas);
        [$altas, $errores, $vistos] = [[], [], []];

        foreach ($filas as $numero => $fila) {
            $fila['id_area_asignada'] = $areas[mb_strtolower($fila['nombre_area'])] ?? null;
            $motivo = $this->validarFila($fila, $existentes, $vistos);
            $alta = null;

            try {
                $alta = $motivo === null ? $this->prepararAlta($actor, $institucion, $fila, $reglas) : null;
            } catch (ReglaNegocioException $excepcion) {
                $motivo = $excepcion->getMessage();
            }

            $vistos[] = mb_strtolower($fila['correo_institucional']);

            if ($motivo !== null || $alta === null) {
                $errores[] = sprintf('Fila %d: %s', $numero, mb_strtolower($motivo));

                continue;
            }

            $altas[] = $alta;
        }

        return [$altas, $errores];
    }

    /**
     * Formato y unicidad de una fila (en el archivo y en todo el SPEI, DT-012).
     *
     * @param  array<string, mixed>  $fila  Valores de la fila.
     * @param  list<string>  $existentes  Correos ya registrados.
     * @param  list<string>  $vistos  Correos de filas anteriores.
     * @return string|null
     */
    private function validarFila(array $fila, array $existentes, array $vistos): ?string
    {
        $validador = Validator::make($fila, [
            'correo_institucional' => ['required', 'email', 'max:150'],
            'nombre_completo' => ['required', 'string', 'max:150'],
        ]);

        if ($validador->fails()) {
            return (string) $validador->errors()->first();
        }

        $correo = mb_strtolower($fila['correo_institucional']);

        return match (true) {
            in_array($correo, $existentes, true) => 'El correo ya está registrado en el SPEI.',
            in_array($correo, $vistos, true) => 'El correo está repetido en el archivo.',
            $fila['nombre_area'] !== '' && $fila['id_area_asignada'] === null => 'El área no existe.',
            default => null,
        };
    }

    /**
     * Correos del archivo que ya existen en cualquier institución (la unicidad es global, DT-012).
     *
     * @param  array<int, array<string, string>>  $filas  Filas del archivo.
     * @return list<string>
     */
    private function obtenerCorreosExistentes(array $filas): array
    {
        $correos = array_map('mb_strtolower', array_column($filas, 'correo_institucional'));

        return Usuario::withoutGlobalScopes()
            ->whereIn('correo_institucional', $correos)
            ->pluck('correo_institucional')
            ->map(fn (string $correo): string => mb_strtolower($correo))
            ->all();
    }

    /**
     * Inserta la cuenta inactiva con una contraseña aleatoria que nunca se comunica (DT-025).
     *
     * @param  Institucion  $institucion  Tenant.
     * @param  array{nombre_completo: string, correo_institucional: string, rol: Rol,
     *     id_area_asignada: int|null}  $alta  Alta validada.
     * @return Usuario
     */
    private function crear(Institucion $institucion, array $alta): Usuario
    {
        $usuario = Usuario::query()->create([
            'id_institucion' => $institucion->id_institucion,
            'nombre_completo' => $alta['nombre_completo'],
            'correo_institucional' => $alta['correo_institucional'],
            'contrasena_hash' => Str::password(40),
            'rol' => $alta['rol'],
            'id_area_asignada' => $alta['id_area_asignada'],
            'activo' => false,
        ]);

        return $usuario->refresh();
    }

    /**
     * Indica si la cuenta tiene préstamos que aún requieren entrega o devolución.
     *
     * @param  Usuario  $usuario  Cuenta.
     * @return bool
     */
    private function tienePrestamosAbiertos(Usuario $usuario): bool
    {
        return Prestamo::query()
            ->where('id_usuario', $usuario->id_usuario)
            ->whereIn('estado', [EstadoPrestamo::Pendiente->value, EstadoPrestamo::Activo->value])
            ->exists();
    }
}
