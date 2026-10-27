<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.3: Administración de Usuarios, Roles y Asistentes
 * @file         2026_10_27_000001_create_dominios_rol_table.php
 * @path         database/migrations/2026_10_27_000001_create_dominios_rol_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla dominios_rol: reglas de cada institución que
 *               asignan el rol de usuario final según el dominio o subdominio
 *               del correo institucional (MD 5.4, RF-017, RN-001).
 *
 * @requisitos   RF-016, RF-017, RN-001, RS-006
 *
 * @see          docs/decisiones/BBB_DT_Registro_v1.0.md (DT-034); docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla dominios_rol.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('dominios_rol', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Reglas por institución que asignan el rol según el dominio del correo.');

            $table->bigIncrements('id_dominio_rol')->comment('Identificador único de la regla.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece (DT-008).');
            $table->string('dominio', 150)->comment('Dominio o subdominio del correo, en minúsculas.');
            $table->enum('rol', ['alumno', 'docente'])->comment('Rol que se asigna a los correos del dominio.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->nullable()->comment('Último cambio.');

            $table->unique(['id_institucion', 'dominio'], 'uk_dominios_rol_institucion_dominio');
            $table->foreign('id_institucion', 'fk_dominios_rol_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Elimina la tabla dominios_rol.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('dominios_rol');
    }
};
