<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.8: Historias, Reportes e Inteligencia Operativa
 * @file         ResumenAuditoria.php
 * @path         app/Support/ResumenAuditoria.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Texto legible de un evento de auditoría: registro afectado
 *               (folio en préstamos) y cambios "campo: antes → después".
 *
 * @requisitos   RF-052, RF-053, RNF-005
 *
 * @see          app/Models/LogAuditoria.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

use App\Models\LogAuditoria;

/**
 * Descripción de eventos de la bitácora.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ResumenAuditoria
{
    /**
     * Columnas técnicas que no aportan al resumen.
     */
    private const IGNORADAS = ['fecha_creacion', 'fecha_actualizacion', 'fecha_hora', 'contrasena_hash'];

    /**
     * Frases para eventos sin cambio de columnas.
     */
    private const FRASES = [
        'INICIAR_SESION' => 'Inicio de sesión',
        'CERRAR_SESION' => 'Cierre de sesión',
        'CREAR' => 'Alta del registro',
        'ELIMINAR' => 'Baja del registro',
        'IMPORTAR' => 'Importación de recursos',
        'SOLICITAR_PRESTAMO' => 'Solicitud registrada',
    ];

    /**
     * Identificador legible del registro afectado.
     *
     * @param  LogAuditoria  $log  Evento.
     * @return string
     */
    public static function registro(LogAuditoria $log): string
    {
        if ($log->tabla_afectada !== 'prestamos') {
            return '#' . $log->registro_id;
        }

        $alta = $log->detalle_nuevo['fecha_creacion'] ?? null;
        $anio = is_string($alta) ? substr($alta, 0, 4) : $log->fecha_hora->format('Y');

        return sprintf('PR-%s-%04d', $anio, $log->registro_id);
    }

    /**
     * Cambios del evento o una frase si no hubo cambios de columnas.
     *
     * @param  LogAuditoria  $log  Evento.
     * @return string
     */
    public static function detalle(LogAuditoria $log): string
    {
        $anterior = is_array($log->detalle_anterior) ? $log->detalle_anterior : [];
        $nuevo = is_array($log->detalle_nuevo) ? $log->detalle_nuevo : [];

        if ($log->accion === 'IMPORTAR') {
            return 'Importación de ' . ($nuevo['total'] ?? 0) . ' recursos';
        }

        $cambios = [];

        foreach ($nuevo as $columna => $valor) {
            if (in_array($columna, self::IGNORADAS, true) || ! array_key_exists($columna, $anterior)) {
                continue;
            }

            if ($anterior[$columna] !== $valor) {
                $cambios[] = $columna . ': ' . self::formatear($anterior[$columna]) . ' → ' . self::formatear($valor);
            }
        }

        if ($cambios !== []) {
            return implode('; ', array_slice($cambios, 0, 3));
        }

        return self::FRASES[$log->accion] ?? ucfirst(mb_strtolower(str_replace('_', ' ', $log->accion)));
    }

    /**
     * Valor corto para el resumen.
     *
     * @param  mixed  $valor  Valor de la columna.
     * @return string
     */
    private static function formatear(mixed $valor): string
    {
        return match (true) {
            $valor === null => '—',
            is_bool($valor) => $valor ? 'sí' : 'no',
            is_scalar($valor) => mb_strimwidth((string) $valor, 0, 40, '…'),
            default => '…',
        };
    }
}
