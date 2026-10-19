<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         2026_10_18_000006_create_logs_auditoria_table.php
 * @path         database/migrations/2026_10_18_000006_create_logs_auditoria_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la bitácora inalterable de auditoría. Los triggers
 *               rechazan cualquier UPDATE o DELETE (RNF-005, DT-010); solo la
 *               depuración por ciclo de vida (RN-012) podrá desactivarlos.
 *
 * @requisitos   RF-052, RF-053, RNF-005, RNF-007, RS-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.11; DT-010, DT-024
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
     * Crea la tabla logs_auditoria y sus triggers de inalterabilidad.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Bitácora inalterable de transacciones críticas (RNF-005).');

            $table->bigIncrements('id_log')->comment('Identificador único del log.');
            $table->unsignedBigInteger('id_usuario')->nullable()
                ->comment('Usuario responsable; nulo si la acción la ejecuta el sistema.');
            $table->unsignedBigInteger('id_institucion')->nullable()
                ->comment('Tenant del evento; nulo solo en eventos de plataforma (DT-024).');
            $table->string('accion', 100)->comment('Acción realizada (CREAR, ACTUALIZAR, CAMBIAR_ESTADO, etc.).');
            $table->string('tabla_afectada', 100)->comment('Tabla afectada por la acción.');
            $table->unsignedBigInteger('registro_id')->comment('ID del registro afectado.');
            $table->json('detalle_anterior')->nullable()->comment('Estado anterior del registro.');
            $table->json('detalle_nuevo')->nullable()->comment('Estado nuevo del registro.');
            $table->string('direccion_ip', 45)->nullable()->comment('Dirección IP de origen (IPv4 o IPv6).');
            $table->timestamp('fecha_hora')->useCurrent()
                ->comment('Fecha y hora del evento; cumple la función de fecha_creacion (DT-024).');

            $table->index(['id_institucion', 'fecha_hora'], 'idx_logs_auditoria_institucion_fecha_hora');
            $table->index('id_usuario', 'idx_logs_auditoria_usuario');

            $table->foreign('id_usuario', 'fk_logs_auditoria_usuarios')
                ->references('id_usuario')->on('usuarios')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_institucion', 'fk_logs_auditoria_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(
            "CREATE TRIGGER trg_logs_auditoria_before_update BEFORE UPDATE ON logs_auditoria
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'logs_auditoria es de solo insercion (RNF-005).';
            END",
        );
        DB::unprepared(
            "CREATE TRIGGER trg_logs_auditoria_before_delete BEFORE DELETE ON logs_auditoria
            FOR EACH ROW BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'logs_auditoria es de solo insercion (RNF-005).';
            END",
        );
    }

    /**
     * Elimina los triggers y la tabla logs_auditoria.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_logs_auditoria_before_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_logs_auditoria_before_update');
        Schema::dropIfExists('logs_auditoria');
    }
};
