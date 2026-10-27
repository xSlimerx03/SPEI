<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         2026_10_27_000004_add_no_entregado_to_detalle_prestamo.php
 * @path         database/migrations/2026_10_27_000004_add_no_entregado_to_detalle_prestamo.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Agrega a detalle_prestamo.condicion_devolucion el valor
 *               no_entregado de MBD Tabla 42, diferido por DT-030 hasta
 *               RF-042: el operador cierra un préstamo atrasado cuyo recurso
 *               no se devolvió.
 *
 * @requisitos   RF-039, RF-042
 *
 * @see          DT-030; docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplía el ENUM con no_entregado.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE detalle_prestamo MODIFY condicion_devolucion
            ENUM('bueno', 'regular', 'malo', 'no_entregado', 'danado') NULL DEFAULT NULL
            COMMENT 'Condición final; no_entregado si el recurso no se devolvió.'",
        );
    }

    /**
     * Restaura el ENUM anterior cuando no hay registros no_entregado.
     *
     * @return void
     * @throws LogicException Si aún existen registros con el valor no_entregado.
     */
    public function down(): void
    {
        $hayRegistrosNoEntregados = DB::table('detalle_prestamo')
            ->where('condicion_devolucion', 'no_entregado')
            ->exists();

        if ($hayRegistrosNoEntregados) {
            throw new LogicException(
                'No se puede revertir la migración mientras existan registros no_entregado; resuélvelos antes.',
            );
        }

        DB::statement(
            "ALTER TABLE detalle_prestamo MODIFY condicion_devolucion
            ENUM('bueno', 'regular', 'malo', 'danado') NULL DEFAULT NULL COMMENT 'Condición final.'",
        );
    }
};
