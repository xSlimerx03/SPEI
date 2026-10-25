<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas
 * @file         Pest.php
 * @path         tests/Pest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Arranque de Pest: las pruebas Feature usan la aplicación
 *               Laravel y una base spei_testing limpia en cada prueba.
 *               Incluye los escenarios de préstamo compartidos por las
 *               pruebas de interfaz y de alcance.
 *
 * @requisitos   RNF-005
 *
 * @see          Manual de Programación v1.2, sección 3.4
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\Rol;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/**
 * Institución con un área, un recurso disponible, un operador y un alumno.
 *
 * @return object{institucion: Institucion, area: Area, recurso: Recurso, operador: Usuario, alumno: Usuario}
 */
function crearEscenario(): object
{
    $institucion = Institucion::factory()->create(['limite_recursos' => 50]);
    $area = Area::factory()->deInstitucion($institucion)->create(['nombre_area' => 'Audiovisual']);

    return (object) [
        'institucion' => $institucion,
        'area' => $area,
        'recurso' => Recurso::factory()->deArea($area)->create(['nombre_recurso' => 'Proyector Epson']),
        'operador' => Usuario::factory()->deInstitucion($institucion)->conRol(Rol::AdminSistema)->create(),
        'alumno' => Usuario::factory()->deInstitucion($institucion)->conRol(Rol::Alumno)->create(),
    ];
}

/**
 * Registra una solicitud del alumno por la interfaz y la devuelve.
 */
function solicitarComoAlumno(TestCase $prueba, Usuario $alumno, Recurso $recurso, string $inicio = '10:00'): Prestamo
{
    $prueba->actingAs($alumno)->post(route('prestamos.store'), [
        'recursos' => [$recurso->id_recurso],
        'fecha' => today()->toDateString(),
        'hora_inicio' => $inicio,
        'hora_fin' => '12:00',
        'motivo' => 'Presentación de avances de tesis.',
        'acepta_reglamento' => '1',
    ])->assertSessionHasNoErrors()->assertRedirect(route('prestamos.mis'));

    return Prestamo::query()->latest('id_prestamo')->firstOrFail();
}
