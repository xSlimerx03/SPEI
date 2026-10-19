<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         2026_10_18_000007_create_notificaciones_table.php
 * @path         database/migrations/2026_10_18_000007_create_notificaciones_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea la tabla notificaciones (en el sistema y por correo). Se
 *               adelanta al bloque A para las alertas de vencimiento (RF-005,
 *               DT-024) e incluye id_institucion (DT-008).
 *
 * @requisitos   RF-005, RF-045, RF-046, RF-047, RF-048, RF-049, RF-050, RNF-004, RNF-006
 *
 * @see          Manual de Base de Datos v1.2, sección 4.12 y Tabla 27; DT-008, DT-022, DT-024
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla notificaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->comment('Notificaciones enviadas a los usuarios en el sistema y por correo.');

            $table->bigIncrements('id_notificacion')->comment('Identificador único de la notificación.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece (DT-008).');
            $table->unsignedBigInteger('id_usuario')->comment('Usuario destinatario.');
            $table->enum('tipo_notificacion', ['push', 'email', 'ambas'])->default('push')
                ->comment('Canal: push = en el sistema web (DT-022), email o ambas.');
            $table->string('titulo', 150)->comment('Título de la notificación.');
            $table->text('mensaje')->comment('Contenido del mensaje.');
            $table->boolean('leida')->default(false)->comment('Si fue leída por el usuario.');
            $table->dateTime('fecha_envio')->nullable()->comment('Fecha y hora de envío efectivo.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');

            $table->index(['id_institucion', 'id_usuario', 'leida'], 'idx_notificaciones_institucion_usuario_leida');
            $table->index(['id_usuario', 'leida'], 'idx_notificaciones_usuario_leida');

            $table->foreign('id_institucion', 'fk_notificaciones_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_usuario', 'fk_notificaciones_usuarios')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Elimina la tabla notificaciones.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
