<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         2026_10_27_000006_create_historial_comprimido_table.php
 * @path         database/migrations/2026_10_27_000006_create_historial_comprimido_table.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Crea historial_comprimido (MBD 4.14): referencia a los archivos
 *               comprimidos con el historial de préstamos de más de un año y
 *               su eliminación programada a los cuatro años (RN-012).
 *
 * @requisitos   RF-052, RN-012, RS-006
 *
 * @see          docs/bd/BBB_DDL03_TablasUsuariosSanciones_v1.0.md; DT-009
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
     * Crea la tabla historial_comprimido.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('historial_comprimido', function (Blueprint $table): void {
            $table->engine('InnoDB');
            $table->comment('Archivos comprimidos del historial operativo y su eliminación programada (RN-012).');

            $table->bigIncrements('id_historial')->comment('Identificador único del histórico comprimido.');
            $table->unsignedBigInteger('id_institucion')->comment('Tenant al que pertenece.');
            $table->string('archivo_comprimido_url', 255)
                ->comment('Ruta del archivo comprimido en el disco configurado.');
            $table->date('periodo_inicio')->comment('Inicio del periodo comprimido.');
            $table->date('periodo_fin')->comment('Fin del periodo comprimido.');
            $table->date('fecha_eliminacion_programada')->comment('periodo_fin + 3 años.');
            $table->boolean('eliminado')->default(false)->comment('Si el archivo ya fue eliminado.');
            $table->timestamp('fecha_creacion')->useCurrent()->comment('Fecha de alta.');

            $table->unique(
                ['id_institucion', 'periodo_inicio', 'periodo_fin'],
                'uk_historial_comprimido_institucion_periodo',
            );
            $table->index(
                ['id_institucion', 'eliminado', 'fecha_eliminacion_programada'],
                'idx_historial_comprimido_id_institucion_eliminacion',
            );
            $table->foreign('id_institucion', 'fk_historial_comprimido_instituciones')
                ->references('id_institucion')->on('instituciones')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Valida la reversión de no_entregado y elimina la tabla historial_comprimido.
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
                'No se puede revertir el bloque mientras existan registros no_entregado; resuélvelos antes.',
            );
        }

        Schema::dropIfExists('historial_comprimido');
    }
};
