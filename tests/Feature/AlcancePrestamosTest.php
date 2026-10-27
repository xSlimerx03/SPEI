<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas de seguridad
 * @file         AlcancePrestamosTest.php
 * @path         tests/Feature/AlcancePrestamosTest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pruebas de mínimo privilegio sobre préstamos: ámbito por área en
 *               panel, menú, mostrador, comprobante y exportación; aislamiento
 *               de auditoría y reportes entre instituciones; neutralización de
 *               fórmulas en CSV y permisos de cancelación.
 *
 * @requisitos   RF-028, RF-029, RF-034, RF-052, RF-053, RF-054, RS-006
 *
 * @see          RED-01 del 25/10/2026 (H-06, H-07, H-08, H-09, H-16, H-17)
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\EstadoRecurso;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Area;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\PrestamoService;
use App\Services\ReporteService;
use App\Support\MenuNavegacion;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
    $this->travelTo(now()->startOfWeek()->addDays(2)->setTime(9, 0));
});

/**
 * Contador de la píldora de Préstamos en el menú del usuario.
 *
 * @param  Usuario  $usuario  Operador.
 * @return int|null
 */
function contarEnMenu(Usuario $usuario): ?int
{
    foreach (app(MenuNavegacion::class)->obtenerSecciones($usuario) as $seccion) {
        foreach ($seccion['enlaces'] as $enlace) {
            if ($enlace['ruta'] === 'prestamos.index') {
                return $enlace['contador'];
            }
        }
    }

    return null;
}

it('limita panel, menú, mostrador, comprobante y exportación al área asignada', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $otraArea = Area::factory()->deInstitucion($e->institucion)->create(['nombre_area' => 'Laboratorio']);
    $ajeno = Recurso::factory()->deArea($otraArea)->create(['nombre_recurso' => 'Osciloscopio Rigol']);
    $propio = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $deOtraArea = solicitarComoAlumno($this, $e->alumno, $ajeno);
    $asistente = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Asistente)
        ->create(['id_area_asignada' => $e->area->id_area]);
    $responsable = Usuario::factory()->responsableDe($e->area)->create();

    expect(contarEnMenu($e->operador))->toBe(2)->and(contarEnMenu($responsable))->toBe(1);

    // El aviso de la última solicitud queda en la sesión de la prueba y menciona el folio ajeno.
    $this->flushSession();
    $this->actingAs($responsable)->get(route('prestamos.index', ['p' => $deOtraArea->id_prestamo]))->assertOk()
        ->assertSee($propio->obtenerFolio())->assertDontSee($deOtraArea->obtenerFolio());
    $csv = $this->get(route('prestamos.exportar'))->assertOk()->streamedContent();
    expect($csv)->toContain($propio->obtenerFolio());
    expect($csv)->not->toContain($deOtraArea->obtenerFolio());

    $this->travelTo(today()->setTime(10, 5));
    $codigo = $this->actingAs($e->alumno)->postJson(route('prestamos.token', $deOtraArea))->json('codigo');
    $this->actingAs($asistente)->post(route('mostrador.validar'), ['codigo' => $codigo])->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertSee('El préstamo pertenece a un área que usted no atiende.');
    $this->post(route('prestamos.confirmar', $deOtraArea), ['codigo' => $codigo, 'condicion' => 'bueno'])
        ->assertForbidden();
    $this->get(route('prestamos.show', $deOtraArea))->assertForbidden();
    expect($deOtraArea->fresh()->estado)->toBe('pendiente');
});

it('permite cancelar solo al propietario y al administrador del sistema', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $motivo = ['motivo_cancelacion' => 'Cambio de sede por mantenimiento eléctrico.'];

    foreach ([Rol::AdminInstitucional, Rol::Asistente] as $rol) {
        $usuario = Usuario::factory()->deInstitucion($e->institucion)->conRol($rol)
            ->create(['id_area_asignada' => $rol === Rol::Asistente ? $e->area->id_area : null]);
        $this->actingAs($usuario)->post(route('prestamos.cancelar', $prestamo), $motivo)->assertForbidden();
    }

    $this->actingAs($e->operador)->get(route('prestamos.index', ['p' => $prestamo->id_prestamo]))->assertOk()
        ->assertSee('Cancelar reserva')->assertDontSee('prioridad institucional')->assertDontSee('de 3 marcas');
    $this->actingAs($e->alumno)->get(route('prestamos.mis'))->assertOk()
        ->assertSee('Cuenta activa')->assertSee('0 de 3 marcas');
    expect($prestamo->fresh()->estado)->toBe('pendiente');
});

it('neutraliza fórmulas al exportar préstamos a CSV', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $prestamo->forceFill(['motivo' => '=HIPERVINCULO("http://ejemplo.test")'])->save();

    $csv = $this->actingAs($e->operador)->get(route('prestamos.exportar'))->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->streamedContent();

    expect($csv)->toStartWith("\xEF\xBB\xBF")->toContain("'=HIPERVINCULO");
    expect($csv)->not->toContain(',=HIPERVINCULO');
});

it('serializa las reservas: un recurso no se reserva dos veces y el lock ocupado rechaza', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $docente = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Docente)->create();
    $datos = [
        'recursos' => [$e->recurso->id_recurso],
        'fecha_hora_inicio' => today()->setTime(10, 0)->toDateTimeString(),
        'fecha_hora_fin' => today()->setTime(12, 0)->toDateTimeString(),
        'motivo' => 'Práctica de laboratorio.',
    ];

    // Simula una operación simultánea de la misma institución que retiene el lock de Redis.
    // NOTE: block() mide su espera con now(); el reloj se libera para que el timeout de 5 s transcurra.
    $momento = now();
    $this->travelBack();
    $candado = Cache::lock('prestamos:institucion:' . $e->institucion->id_institucion, 30);
    expect($candado->get())->toBeTrue();
    expect(fn (): Prestamo => app(PrestamoService::class)->solicitar($e->alumno, $datos))
        ->toThrow(ReglaNegocioException::class, 'Hay otra operación en curso.');
    $candado->release();
    $this->travelTo($momento);

    $otroAlumno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();
    $primero = app(PrestamoService::class)->solicitar($e->alumno, $datos);
    expect(fn (): Prestamo => app(PrestamoService::class)->solicitar($otroAlumno, $datos))
        ->toThrow(ReglaNegocioException::class, 'Uno de los recursos ya está reservado en ese horario.');
    expect(Prestamo::query()->count())->toBe(1)
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible);

    // RN-008 dentro de la ventana de concurrencia: el docente desplaza la reserva del alumno.
    $segundo = app(PrestamoService::class)->solicitar($docente, $datos);
    expect($primero->fresh()->estado)->toBe('cancelado')
        ->and($primero->fresh()->motivo_cancelacion)->toContain('RN-008')
        ->and($segundo->estado)->toBe('pendiente');
});

it('aplica RN-008 solo a solicitudes concurrentes y reserva periodos sin traslape', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $docente = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Docente)->create();
    $alumnoConMarcas = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)
        ->create(['marcas_acumuladas' => 2]);
    $periodo = fn (int $inicio, int $fin): array => [
        'recursos' => [$e->recurso->id_recurso],
        'fecha_hora_inicio' => today()->setTime($inicio, 0)->toDateTimeString(),
        'fecha_hora_fin' => today()->setTime($fin, 0)->toDateTimeString(),
        'motivo' => 'Práctica de laboratorio.',
    ];
    $servicio = app(PrestamoService::class);

    // Mejor historial: con el mismo rol gana quien tiene menos marcas.
    $conMarcas = $servicio->solicitar($alumnoConMarcas, $periodo(10, 12));
    $sinMarcas = $servicio->solicitar($e->alumno, $periodo(11, 13));
    expect($conMarcas->fresh()->estado)->toBe('cancelado')->and($sinMarcas->estado)->toBe('pendiente');

    // Fuera de la ventana de concurrencia se respeta la reserva aunque el docente tenga prioridad.
    $this->travel((int) config('spei.prestamos.ventana_prioridad_minutos') + 1)->minutes();
    expect(fn (): Prestamo => $servicio->solicitar($docente, $periodo(12, 14)))
        ->toThrow(ReglaNegocioException::class, 'ya está reservado');

    // Periodos contiguos del mismo recurso no chocan.
    $tarde = $servicio->solicitar($docente, $periodo(13, 15));
    expect($tarde->estado)->toBe('pendiente')
        ->and(Prestamo::query()->whereIn('estado', ['pendiente'])->count())->toBe(2);
});

it('aísla auditoría y reportes entre instituciones', function (): void {
    /** @var TestCase $this */
    $propia = crearEscenario();
    $ajena = crearEscenario();
    $ajena->alumno->forceFill(['nombre_completo' => 'Zacarías Ajeno'])->save();
    $ajena->recurso->forceFill(['nombre_recurso' => 'Cámara Ajena'])->save();
    foreach ([$propia, $ajena] as $escenario) {
        solicitarComoAlumno($this, $escenario->alumno, $escenario->recurso)
            ->forceFill(['estado' => 'activo'])->save();
    }

    $reporte = app(ReporteService::class)->generar($propia->operador, 7, null);
    expect($reporte['total'])->toBe(1)
        ->and(array_column($reporte['masPrestados'], 'nombre'))->toBe(['Proyector Epson']);

    $this->actingAs($propia->operador)->get(route('reportes.index', ['periodo' => 7]))->assertOk()
        ->assertSee('Proyector Epson')->assertDontSee('Cámara Ajena');
    $this->get(route('auditoria.index'))->assertOk()->assertDontSee('Zacarías Ajeno');
    $csv = $this->get(route('auditoria.exportar'))->assertOk()->streamedContent();
    expect($csv)->not->toContain('Zacarías Ajeno');
    expect(Prestamo::query()->withoutGlobalScopes()->count())->toBe(2);
});
