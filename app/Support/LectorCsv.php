<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Soporte
 * @file         LectorCsv.php
 * @path         app/Support/LectorCsv.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Lectura común de archivos CSV de carga masiva (catálogo y
 *               usuarios): quita el BOM, exige el encabezado exacto y limita
 *               el número de filas.
 *
 * @requisitos   RF-012, RF-016
 *
 * @see          CU-008, CU-016
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Lector de CSV con encabezado fijo.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class LectorCsv
{
    /**
     * Lee el CSV y devuelve las filas indexadas por el número de línea que ve el usuario (encabezado = 1).
     *
     * @param  UploadedFile  $archivo  Archivo recibido.
     * @param  list<string>  $encabezado  Columnas obligatorias, en orden.
     * @param  string  $campo  Campo del formulario al que se asocian los errores.
     * @return array<int, array<string, string>>
     *
     * @throws ValidationException Si el encabezado no coincide, no hay filas o se excede el máximo.
     */
    public static function leer(UploadedFile $archivo, array $encabezado, string $campo = 'archivo'): array
    {
        $contenido = (string) file_get_contents($archivo->getRealPath());
        // Se quita el BOM que agregan algunas hojas de cálculo al exportar en UTF-8.
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido) ?? $contenido;
        $lineas = preg_split('/\r\n|\n|\r/', trim($contenido)) ?: [];
        $recibido = array_map('trim', str_getcsv((string) array_shift($lineas), ',', '"', ''));

        if ($recibido !== $encabezado) {
            throw ValidationException::withMessages([$campo => 'El encabezado del archivo no es válido.']);
        }

        $filas = [];

        foreach ($lineas as $indice => $linea) {
            if (trim($linea) === '') {
                continue;
            }

            $valores = array_pad(array_map('trim', str_getcsv($linea, ',', '"', '')), count($encabezado), '');
            $filas[$indice + 2] = array_combine($encabezado, array_slice($valores, 0, count($encabezado)));
        }

        self::validarTamano(count($filas), $campo);

        return $filas;
    }

    /**
     * Rechaza archivos sin filas o con más filas de las permitidas.
     *
     * @param  int  $total  Filas de datos.
     * @param  string  $campo  Campo del formulario.
     * @return void
     *
     * @throws ValidationException Si el total no es válido.
     */
    private static function validarTamano(int $total, string $campo): void
    {
        $maximo = (int) config('spei.importacion.maximo_filas');

        if ($total === 0) {
            throw ValidationException::withMessages([$campo => 'El archivo no contiene filas.']);
        }

        if ($total > $maximo) {
            throw ValidationException::withMessages([
                $campo => sprintf('El archivo supera el máximo de %s filas.', number_format($maximo, 0, '.', ' ')),
            ]);
        }
    }
}
