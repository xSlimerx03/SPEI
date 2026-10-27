<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         ImportacionCatalogoService.php
 * @path         app/Services/ImportacionCatalogoService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Carga masiva de recursos desde CSV: valida todas las filas
 *               antes de insertar e inserta todo o nada en una transacción.
 *
 * @requisitos   RF-012, RN-010, RNF-005, RS-006
 *
 * @see          docs/requisitos/BBB_CU008_ImportarCatalogo_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoRecurso;
use App\Enums\TipoRecurso;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Support\LectorCsv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Importación del catálogo de recursos por archivo CSV.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ImportacionCatalogoService
{
    /**
     * Encabezado obligatorio del archivo, en este orden.
     */
    public const ENCABEZADO = [
        'nombre_recurso',
        'numero_inventario',
        'numero_serie',
        'tipo_recurso',
        'marca',
        'anio_adquisicion',
        'nombre_area',
    ];

    /**
     * Recibe las dependencias por inyección del contenedor.
     *
     * @param  AuditoriaService  $auditoriaService  Dependencia inyectada.
     * @param  RecursoService  $recursoService  Dependencia inyectada.
     */
    public function __construct(
        private AuditoriaService $auditoriaService,
        private RecursoService $recursoService,
    ) {}

    /**
     * Importa el archivo y devuelve el número de recursos creados.
     *
     * @param  Usuario  $actor  Administrador que importa.
     * @param  Institucion  $institucion  Tenant del actor.
     * @param  UploadedFile  $archivo  Archivo CSV validado por tipo y tamaño.
     * @return int
     * @throws ValidationException Si el encabezado o alguna fila no son válidos.
     */
    public function importar(Usuario $actor, Institucion $institucion, UploadedFile $archivo): int
    {
        $filas = LectorCsv::leer($archivo, self::ENCABEZADO);
        $areas = $this->obtenerAreasPermitidas($actor);
        $errores = $this->validarFilas($filas, $areas);

        if ($errores !== []) {
            throw ValidationException::withMessages(['archivo' => $errores]);
        }

        return DB::transaction(function () use ($filas, $areas, $institucion): int {
            $institucion = Institucion::query()->lockForUpdate()->findOrFail($institucion->id_institucion);
            $this->recursoService->validarCupo($institucion, count($filas));
            $ids = [];

            foreach ($filas as $fila) {
                $ids[] = $this->insertarFila($fila, $areas, $institucion);
            }

            $this->auditoriaService->registrarMasivo('IMPORTAR', 'recursos', $ids[0], $institucion->id_institucion, [
                'total' => count($ids),
                'primer_id' => $ids[0],
                'ultimo_id' => $ids[count($ids) - 1],
            ]);

            return count($ids);
        });
    }

    /**
     * Áreas activas en las que el actor puede registrar, indexadas por nombre en minúsculas.
     *
     * @param  Usuario  $actor  Administrador que importa.
     * @return array<string, int>
     */
    private function obtenerAreasPermitidas(Usuario $actor): array
    {
        return Area::query()
            ->activas()
            ->when(
                $actor->esResponsableDeArea(),
                fn (Builder $consulta): Builder => $consulta->whereKey($actor->id_area_asignada),
            )
            ->get(['id_area', 'nombre_area'])
            ->mapWithKeys(fn (Area $area): array => [mb_strtolower($area->nombre_area) => $area->id_area])
            ->all();
    }

    /**
     * Valida cada fila y la unicidad de inventario y serie en el archivo y en la BD.
     *
     * @param  array<int, array<string, string>>  $filas  Filas por número de línea.
     * @param  array<string, int>  $areas  Áreas permitidas.
     * @return list<string>
     */
    private function validarFilas(array $filas, array $areas): array
    {
        $errores = [];
        $existentes = $this->obtenerCodigosExistentes($filas);
        $vistos = ['numero_inventario' => [], 'numero_serie' => []];

        foreach ($filas as $numero => $fila) {
            $motivo = $this->validarCampos($fila, $areas)
                ?? $this->validarUnicidad($fila, $existentes, $vistos);

            if ($motivo !== null) {
                $errores[] = sprintf('Fila %d: %s', $numero, $motivo);
            }

            $vistos['numero_inventario'][] = mb_strtolower($fila['numero_inventario']);

            if ($fila['numero_serie'] !== '') {
                $vistos['numero_serie'][] = mb_strtolower($fila['numero_serie']);
            }
        }

        return $errores;
    }

    /**
     * Valida tipos, longitudes, año y área de una fila.
     *
     * @param  array<string, string>  $fila  Valores de la fila.
     * @param  array<string, int>  $areas  Áreas permitidas.
     * @return string|null Motivo del rechazo o null si es válida.
     */
    private function validarCampos(array $fila, array $areas): ?string
    {
        $validador = Validator::make($fila, [
            'nombre_recurso' => ['required', 'string', 'max:150'],
            'numero_inventario' => ['required', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'tipo_recurso' => ['required', Rule::enum(TipoRecurso::class)],
            'marca' => ['nullable', 'string', 'max:100'],
            'anio_adquisicion' => ['nullable', 'integer', 'between:1990,' . now()->year],
            'nombre_area' => ['required', 'string'],
        ]);

        if ($validador->fails()) {
            return mb_strtolower((string) $validador->errors()->first());
        }

        if (! array_key_exists(mb_strtolower($fila['nombre_area']), $areas)) {
            return 'el área no existe, está inactiva o no le corresponde.';
        }

        return null;
    }

    /**
     * Verifica que inventario y serie no se repitan en el archivo ni en la institución.
     *
     * @param  array<string, string>  $fila  Valores de la fila.
     * @param  array{numero_inventario: list<string>, numero_serie: list<string>}  $existentes  Códigos en la BD.
     * @param  array{numero_inventario: list<string>, numero_serie: list<string>}  $vistos  Códigos de filas previas.
     * @return string|null
     */
    private function validarUnicidad(array $fila, array $existentes, array $vistos): ?string
    {
        $inventario = mb_strtolower($fila['numero_inventario']);
        $serie = mb_strtolower($fila['numero_serie']);

        if (in_array($inventario, $existentes['numero_inventario'], true)) {
            return 'el número de inventario ya está registrado.';
        }

        if (in_array($inventario, $vistos['numero_inventario'], true)) {
            return 'el número de inventario está repetido en el archivo.';
        }

        if ($serie !== '' && in_array($serie, $existentes['numero_serie'], true)) {
            return 'el número de serie ya está registrado.';
        }

        if ($serie !== '' && in_array($serie, $vistos['numero_serie'], true)) {
            return 'el número de serie está repetido en el archivo.';
        }

        return null;
    }

    /**
     * Obtiene los inventarios y series del archivo que ya existen en la institución.
     *
     * @param  array<int, array<string, string>>  $filas  Filas del archivo.
     * @return array{numero_inventario: list<string>, numero_serie: list<string>}
     */
    private function obtenerCodigosExistentes(array $filas): array
    {
        $resultado = [];

        foreach (['numero_inventario', 'numero_serie'] as $columna) {
            $valores = array_values(array_filter(array_column($filas, $columna), fn (string $v): bool => $v !== ''));
            $resultado[$columna] = Recurso::query()
                ->whereIn($columna, $valores)
                ->pluck($columna)
                ->map(fn (?string $valor): string => mb_strtolower((string) $valor))
                ->values()
                ->all();
        }

        return $resultado;
    }

    /**
     * Inserta una fila ya validada y devuelve el identificador creado.
     *
     * @param  array<string, string>  $fila  Valores de la fila.
     * @param  array<string, int>  $areas  Áreas permitidas.
     * @param  Institucion  $institucion  Tenant bloqueado.
     * @return int
     */
    private function insertarFila(array $fila, array $areas, Institucion $institucion): int
    {
        $recurso = Recurso::query()->create([
            'id_institucion' => $institucion->id_institucion,
            'id_area' => $areas[mb_strtolower($fila['nombre_area'])],
            'nombre_recurso' => $fila['nombre_recurso'],
            'numero_inventario' => $fila['numero_inventario'],
            'numero_serie' => $fila['numero_serie'] === '' ? null : $fila['numero_serie'],
            'tipo_recurso' => $fila['tipo_recurso'],
            'marca' => $fila['marca'] === '' ? null : $fila['marca'],
            'anio_adquisicion' => $fila['anio_adquisicion'] === '' ? null : (int) $fila['anio_adquisicion'],
            'estado' => EstadoRecurso::Disponible,
            'activo' => true,
        ]);

        return (int) $recurso->id_recurso;
    }
}
