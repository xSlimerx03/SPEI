<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         AuditoriaService.php
 * @path         app/Services/AuditoriaService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Registra en logs_auditoria las transacciones críticas con el
 *               usuario, el tenant, la IP y el detalle anterior y nuevo.
 *
 * @requisitos   RF-052, RNF-005, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.11; DT-010, DT-024
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\LogAuditoria;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Escribe la bitácora inalterable de auditoría.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AuditoriaService
{
    /**
     * Toma una fotografía de los atributos visibles del modelo (sin contraseñas).
     *
     * @param  Model  $modelo  Registro a capturar.
     * @return array<string, mixed>
     */
    public function capturar(Model $modelo): array
    {
        return $modelo->attributesToArray();
    }

    /**
     * Registra una acción sobre un registro.
     *
     * @param  string  $accion  Acción en mayúsculas (CREAR, ACTUALIZAR, CAMBIAR_ESTADO, ...).
     * @param  Model  $registro  Registro afectado.
     * @param  array<string, mixed>|null  $anterior  Estado anterior.
     * @param  array<string, mixed>|null  $nuevo  Estado nuevo.
     * @param  Usuario|null  $actor  Responsable; si es null se toma el usuario autenticado.
     * @return LogAuditoria
     */
    public function registrar(
        string $accion,
        Model $registro,
        ?array $anterior = null,
        ?array $nuevo = null,
        ?Usuario $actor = null,
    ): LogAuditoria {
        $idInstitucion = $registro->getAttribute('id_institucion');

        return $this->insertar([
            'id_usuario' => $actor->id_usuario ?? Auth::id(),
            'id_institucion' => $idInstitucion === null ? null : (int) $idInstitucion,
            'accion' => $accion,
            'tabla_afectada' => $registro->getTable(),
            'registro_id' => (int) $registro->getKey(),
            'detalle_anterior' => $anterior,
            'detalle_nuevo' => $nuevo,
        ]);
    }

    /**
     * Registra una operación masiva (por ejemplo, IMPORTAR) que afecta varias filas.
     *
     * @param  string  $accion  Acción en mayúsculas.
     * @param  string  $tabla  Tabla afectada.
     * @param  int  $registroId  Primer registro afectado.
     * @param  int  $idInstitucion  Tenant de la operación.
     * @param  array<string, mixed>  $detalle  Resumen de la operación.
     * @return LogAuditoria
     */
    public function registrarMasivo(
        string $accion,
        string $tabla,
        int $registroId,
        int $idInstitucion,
        array $detalle,
    ): LogAuditoria {
        return $this->insertar([
            'id_usuario' => Auth::id(),
            'id_institucion' => $idInstitucion,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'detalle_anterior' => null,
            'detalle_nuevo' => $detalle,
        ]);
    }

    /**
     * Inserta el log agregando la IP de origen cuando hay una petición HTTP.
     *
     * @param  array<string, mixed>  $atributos  Columnas del log.
     * @return LogAuditoria
     */
    private function insertar(array $atributos): LogAuditoria
    {
        // NOTE: en tareas de consola no hay REMOTE_ADDR y la IP queda nula.
        $atributos['direccion_ip'] = request()->ip();

        return LogAuditoria::query()->create($atributos);
    }
}
