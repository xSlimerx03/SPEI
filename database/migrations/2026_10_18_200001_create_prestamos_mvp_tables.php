<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas / 4.5: Entrega y Devolución
 * @file         2026_10_18_200001_create_prestamos_mvp_tables.php
 * @path         database/migrations/2026_10_18_200001_create_prestamos_mvp_tables.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.0.1
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea de forma aditiva las tablas prestamos, detalle_prestamo y
 *               tokens_qr con id_institucion (DT-008), cascadas de MBD Tabla 27
 *               (DT-009) y el CHECK del periodo. La v1.0.1 solo reformatea el
 *               archivo; los nombres de índices y llaves se normalizan en
 *               2026_10_25_000001_normalizar_nombres_prestamos.php.
 *
 * @requisitos   RF-022, RF-024, RF-034, RF-036, RS-006
 *
 * @see          docs/bd/BBB_DDL03_TablasPrestamos_v1.0.md; Manual de Base de Datos v1.2, secciones 4.6 a 4.8
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
     * Crea las tablas sin modificar registros existentes.
     *
     * @return void
     */
    public function up(): void
    {
        $this->crearPrestamos();
        $this->crearDetalle();
        $this->crearTokens();
        DB::statement(
            'ALTER TABLE prestamos ADD CONSTRAINT chk_prestamos_periodo CHECK (fecha_hora_fin > fecha_hora_inicio)',
        );
    }

    /**
     * Revierte exclusivamente las tablas nuevas, en orden de dependencias.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_qr');
        Schema::dropIfExists('detalle_prestamo');
        Schema::dropIfExists('prestamos');
    }

    /**
     * Crea la cabecera de una solicitud.
     *
     * @return void
     */
    private function crearPrestamos(): void
    {
        Schema::create('prestamos', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Solicitudes y movimientos de préstamo por institución.');
            $table->bigIncrements('id_prestamo')->comment('Identificador del préstamo.');
            $table->unsignedBigInteger('id_institucion')->comment('Institución propietaria.');
            $table->unsignedBigInteger('id_usuario')->comment('Usuario solicitante.');
            $table->dateTime('fecha_hora_inicio')->comment('Inicio programado, zona horaria de la aplicación.');
            $table->dateTime('fecha_hora_fin')->comment('Devolución programada.');
            $table->enum('estado', ['pendiente', 'activo', 'devuelto', 'vencido', 'cancelado'])->default('pendiente')
                ->comment('Estado del préstamo.');
            $table->text('motivo')->comment('Finalidad del préstamo.');
            $table->dateTime('fecha_hora_entrega_real')->nullable()->comment('Confirmación de entrega.');
            $table->dateTime('fecha_hora_devolucion_real')->nullable()->comment('Confirmación de devolución.');
            $table->text('motivo_cancelacion')->nullable()->comment('Justificación de cancelación.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->nullable()->comment('Último cambio.');
            $table->index(['id_institucion', 'estado', 'fecha_hora_inicio'], 'idx_prestamos_institucion_estado_inicio');
            $table->index('id_usuario', 'idx_prestamos_usuario');
            $table->foreign('id_institucion', 'fk_prestamos_instituciones')->references('id_institucion')
                ->on('instituciones')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_usuario', 'fk_prestamos_usuarios')->references('id_usuario')
                ->on('usuarios')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Crea los recursos incluidos en cada solicitud.
     *
     * @return void
     */
    private function crearDetalle(): void
    {
        Schema::create('detalle_prestamo', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Recursos y condiciones de entrega y devolución.');
            $table->bigIncrements('id_detalle')->comment('Identificador del detalle.');
            $table->unsignedBigInteger('id_institucion')->comment('Institución propietaria.');
            $table->unsignedBigInteger('id_prestamo')->comment('Solicitud asociada.');
            $table->unsignedBigInteger('id_recurso')->comment('Recurso solicitado.');
            $table->enum('condicion_entrega', ['bueno', 'regular', 'malo'])->default('bueno')
                ->comment('Condición inicial.');
            $table->enum('condicion_devolucion', ['bueno', 'regular', 'malo', 'danado'])->nullable()
                ->comment('Condición final.');
            $table->text('incidencia')->nullable()->comment('Observaciones de devolución.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');
            $table->timestamp('fecha_actualizacion')->nullable()->comment('Último cambio.');
            $table->unique(['id_institucion', 'id_prestamo', 'id_recurso'], 'uk_detalle_institucion_prestamo_recurso');
            $table->index('id_prestamo', 'idx_detalle_prestamo');
            $table->index('id_recurso', 'idx_detalle_recurso');
            $table->foreign('id_institucion', 'fk_detalle_instituciones')->references('id_institucion')
                ->on('instituciones')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_prestamo', 'fk_detalle_prestamos')->references('id_prestamo')
                ->on('prestamos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_recurso', 'fk_detalle_recursos')->references('id_recurso')
                ->on('recursos')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Crea tokens de un solo uso; nunca guarda el código en claro.
     *
     * @return void
     */
    private function crearTokens(): void
    {
        Schema::create('tokens_qr', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Hash de tokens de custodia de un solo uso.');
            $table->bigIncrements('id_token')->comment('Identificador del token.');
            $table->unsignedBigInteger('id_institucion')->comment('Institución propietaria.');
            $table->unsignedBigInteger('id_prestamo')->comment('Préstamo asociado.');
            $table->enum('tipo_token', ['entrega', 'devolucion'])->comment('Operación autorizada.');
            $table->char('token', 64)->comment('Hash SHA-256 del código aleatorio.');
            $table->dateTime('fecha_expiracion')->comment('Fin de vigencia del token.');
            $table->boolean('usado')->default(false)->comment('Token consumido o sustituido.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de emisión.');
            $table->unique('token', 'uk_tokens_qr_token');
            $table->index(['id_institucion', 'id_prestamo'], 'idx_tokens_institucion_prestamo');
            $table->index('id_prestamo', 'idx_tokens_prestamo');
            $table->foreign('id_institucion', 'fk_tokens_instituciones')->references('id_institucion')
                ->on('instituciones')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('id_prestamo', 'fk_tokens_prestamos')->references('id_prestamo')
                ->on('prestamos')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }
};
