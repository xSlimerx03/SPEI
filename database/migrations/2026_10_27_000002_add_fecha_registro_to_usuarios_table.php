<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         2026_10_27_000002_add_fecha_registro_to_usuarios_table.php
 * @path         database/migrations/2026_10_27_000002_add_fecha_registro_to_usuarios_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Agrega usuarios.fecha_registro para distinguir una invitación
 *               pendiente (NULL) de una cuenta dada de baja (activo = 0 con
 *               fecha). Las cuentas activas existentes toman su fecha de alta.
 *
 * @requisitos   RF-016, RF-018, RF-019, RF-020
 *
 * @see          DT-035; docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna y completa las cuentas ya activas.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dateTime('fecha_registro')->nullable()->after('activo')
                ->comment('Fecha en que completó su registro; NULL = invitación pendiente.');
        });

        DB::table('usuarios')->where('activo', true)->update(['fecha_registro' => DB::raw('fecha_creacion')]);
    }

    /**
     * Elimina la columna.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropColumn('fecha_registro');
        });
    }
};
