<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         2026_10_27_000005_create_conversaciones_table.php
 * @path         database/migrations/2026_10_27_000005_create_conversaciones_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea conversaciones (MBD 4.13): mensajes entre el solicitante
 *               y los operadores del área mientras la solicitud está
 *               pendiente (RF-033), con id_institucion (DT-008) y cascadas de
 *               MBD Tabla 27.
 *
 * @requisitos   RF-033, RNF-004, RS-006
 *
 * @see          docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md; DT-008, DT-009
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla conversaciones.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Mensajes entre solicitante y operadores previos a la entrega de una solicitud.');

            $table->bigIncrements('id_mensaje')->comment('Identificador único del mensaje.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece (DT-008).');
            $table->unsignedBigInteger('id_prestamo')->comment('Préstamo asociado.');
            $table->unsignedBigInteger('id_usuario_emisor')->comment('Usuario que envía el mensaje.');
            $table->text('mensaje')->comment('Contenido del mensaje.');
            $table->boolean('leido')->default(false)->comment('Si fue leído por el destinatario.');
            $table->timestamp('fecha_hora')->useCurrent()->comment('Fecha y hora del mensaje.');

            $table->index(
                ['id_institucion', 'id_prestamo', 'fecha_hora'],
                'idx_conversaciones_institucion_prestamo_fecha',
            );
            $table->index('id_prestamo', 'idx_conversaciones_prestamo');
            $table->index('id_usuario_emisor', 'idx_conversaciones_usuario_emisor');
            $table->foreign('id_institucion', 'fk_conversaciones_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_prestamo', 'fk_conversaciones_prestamos')
                ->references('id_prestamo')->on('prestamos')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_usuario_emisor', 'fk_conversaciones_usuarios')
                ->references('id_usuario')->on('usuarios')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Elimina la tabla conversaciones.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};
