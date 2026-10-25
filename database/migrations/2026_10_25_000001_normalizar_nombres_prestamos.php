<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas / 4.5: Entrega y Devolución
 * @file         2026_10_25_000001_normalizar_nombres_prestamos.php
 * @path         database/migrations/2026_10_25_000001_normalizar_nombres_prestamos.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Normaliza los nombres de llaves e índices de detalle_prestamo y
 *               tokens_qr a la nomenclatura del MBD (fk_<origen>_<destino>,
 *               uk_<tabla>_<cols>, idx_<tabla>_<cols>). No cambia columnas,
 *               datos, cascadas ni cobertura de índices: solo renombra
 *               índices y vuelve a crear cada FK con su nombre oficial.
 *
 * @requisitos   RS-006, RF-024, RF-034, RF-036
 *
 * @see          docs/bd/BBB_DDL08_LlavesPrestamos_v1.0.md; RED-01 del 25/10/2026 (H-21); DT-009, DT-026
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices a renombrar por tabla: nombre anterior => nombre oficial.
     *
     * @var array<string, array<string, string>>
     */
    private const INDICES = [
        'detalle_prestamo' => [
            'uk_detalle_institucion_prestamo_recurso' => 'uk_detalle_prestamo_institucion_prestamo_recurso',
            'idx_detalle_prestamo' => 'idx_detalle_prestamo_prestamo',
            'idx_detalle_recurso' => 'idx_detalle_prestamo_recurso',
        ],
        'tokens_qr' => [
            'idx_tokens_institucion_prestamo' => 'idx_tokens_qr_institucion_prestamo',
            'idx_tokens_prestamo' => 'idx_tokens_qr_prestamo',
        ],
    ];

    /**
     * Llaves foráneas por tabla: nombre anterior => [nombre oficial, columna, tabla referenciada, borrado].
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: string, 3: string}>>
     */
    private const LLAVES = [
        'detalle_prestamo' => [
            'fk_detalle_instituciones' => [
                'fk_detalle_prestamo_instituciones', 'id_institucion', 'instituciones', 'restrict',
            ],
            'fk_detalle_prestamos' => ['fk_detalle_prestamo_prestamos', 'id_prestamo', 'prestamos', 'cascade'],
            'fk_detalle_recursos' => ['fk_detalle_prestamo_recursos', 'id_recurso', 'recursos', 'restrict'],
        ],
        'tokens_qr' => [
            'fk_tokens_instituciones' => [
                'fk_tokens_qr_instituciones', 'id_institucion', 'instituciones', 'restrict',
            ],
            'fk_tokens_prestamos' => ['fk_tokens_qr_prestamos', 'id_prestamo', 'prestamos', 'cascade'],
        ],
    ];

    /**
     * Aplica los nombres oficiales.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (array_keys(self::INDICES) as $tabla) {
            $this->renombrar($tabla, false);
        }
    }

    /**
     * Restaura los nombres anteriores.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (array_keys(self::INDICES) as $tabla) {
            $this->renombrar($tabla, true);
        }
    }

    /**
     * Renombra índices y recrea llaves foráneas de una tabla en el sentido indicado.
     *
     * NOTE: MySQL no permite renombrar una restricción FOREIGN KEY; se elimina y se crea de nuevo sobre el
     * mismo índice, sin tocar datos. El índice de soporte se conserva porque tiene nombre propio.
     *
     * @param  string  $tabla  Tabla afectada.
     * @param  bool  $revertir  Verdadero para volver a los nombres anteriores.
     * @return void
     */
    private function renombrar(string $tabla, bool $revertir): void
    {
        Schema::table($tabla, function (Blueprint $table) use ($tabla, $revertir): void {
            foreach (self::LLAVES[$tabla] as $anterior => [$oficial, $columna, $destino, $borrado]) {
                $table->dropForeign($revertir ? $oficial : $anterior);
                $llave = $table->foreign($columna, $revertir ? $anterior : $oficial)
                    ->references($columna)
                    ->on($destino)
                    ->cascadeOnUpdate();
                // DT-009: solo las dependencias de prestamos borran en cascada (MBD Tabla 27).
                if ($borrado === 'cascade') {
                    $llave->cascadeOnDelete();
                } else {
                    $llave->restrictOnDelete();
                }
            }

            foreach (self::INDICES[$tabla] as $anterior => $oficial) {
                $table->renameIndex($revertir ? $oficial : $anterior, $revertir ? $anterior : $oficial);
            }
        });
    }
};
