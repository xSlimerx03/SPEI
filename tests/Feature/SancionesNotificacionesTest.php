<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas del bloque D
 * @file         SancionesNotificacionesTest.php
 * @path         tests/Feature/SancionesNotificacionesTest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pruebas de sanciones automáticas (marcas por trigger, bloqueo,
 *               exención, levantamiento, no devuelto), matriz por institución,
 *               recordatorios y correos, conversación previa, ventana de
 *               recogida e historial comprimido (RN-012).
 *
 * @requisitos   RF-026, RF-033, RF-041, RF-042, RF-043, RF-044, RF-045, RF-046, RF-047, RF-048, RF-049, RF-052,
 *               RN-003, RN-005, RN-012, RS-006
 *
 * @see          CU-019 a CU-023; DT-033, DT-036
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\EstadoRecurso;
use App\Enums\Rol;
use App\Enums\TipoIncidente;
use App\Enums\TipoNotificacion;
use App\Mail\AvisoMail;
use App\Models\Area;
use App\Models\HistorialComprimido;
use App\Models\LogAuditoria;
use App\Models\MatrizSancion;
use App\Models\MensajeConversacion;
use App\Models\Notificacion;
use App\Models\Penalizacion;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\PrestamoService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
    Mail::fake();
    $this->travelTo(now()->startOfWeek()->addDays(2)->setTime(9, 0));
});

/**
 * Entrega a las 10:05 y devuelve a la hora [h, m] indicada con la condición dada.
 *
 * @param  array{0: int, 1: int}  $hora  Hora y minuto de la devolución.
 */
function entregarYDevolver(
    TestCase $prueba,
    object $e,
    Prestamo $prestamo,
    string $condicion,
    array $hora,
): Prestamo {
    $servicio = app(PrestamoService::class);
    $prueba->travelTo(today()->setTime(10, 5));
    $codigo = $servicio->generarToken($e->alumno, $prestamo);
    $servicio->confirmar($e->operador, $prestamo, ['codigo' => $codigo, 'condicion' => 'bueno']);
    $prueba->travelTo(today()->setTime($hora[0], $hora[1]));
    $codigo = $servicio->generarToken($e->alumno, $prestamo->fresh());

    return $servicio->confirmar($e->operador, $prestamo->fresh(), [
        'codigo' => $codigo,
        'condicion' => $condicion,
        'incidencia' => $condicion === 'bueno' ? null : 'Pantalla rayada',
    ]);
}

it('suma marcas por trigger al devolver tarde o con daños y bloquea al alcanzar el umbral', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    entregarYDevolver($this, $e, $prestamo, 'danado', [12, 30]);

    $tipos = Penalizacion::query()->pluck('tipo_penalizacion')->map(fn (TipoIncidente $t): string => $t->value)->all();
    expect($tipos)->toEqualCanonicalizing(['danado', 'retraso'])
        ->and($e->alumno->fresh()->marcas_acumuladas)->toBe(3)
        ->and($e->alumno->fresh()->bloqueado)->toBeTrue()
        ->and($e->alumno->fresh()->fecha_fin_bloqueo?->isAfter(now()->addDays(6)))->toBeTrue()
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Mantenimiento)
        ->and(LogAuditoria::query()->where('accion', 'BLOQUEAR')->exists())->toBeTrue();
    Mail::assertQueued(AvisoMail::class, fn (AvisoMail $correo): bool => str_starts_with($correo->titulo, 'Sanción'));

    $this->actingAs($e->alumno)->get(route('prestamos.mis'))->assertOk()
        ->assertSee('Cuenta bloqueada')->assertSee('Devuelto dañado');
    $otro = Recurso::factory()->deArea($e->area)->create();
    $this->travelTo(today()->addDay()->setTime(9, 0));
    $this->post(route('prestamos.store'), [
        'recursos' => [$otro->id_recurso], 'fecha' => today()->toDateString(), 'hora_inicio' => '10:00',
        'hora_fin' => '11:00', 'motivo' => 'Otra práctica.', 'acepta_reglamento' => '1',
    ])->assertSessionHas('error');
});

it('no sanciona reservas vencidas sin entrega (RN-003) y levanta el bloqueo al cumplirse', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $this->travelTo(today()->setTime(10, 30));
    $this->artisan('spei:vencer-prestamos')->assertSuccessful();
    expect(Penalizacion::query()->count())->toBe(0);

    $e->alumno->forceFill([
        'bloqueado' => true, 'marcas_acumuladas' => 3, 'fecha_bloqueo' => now()->subDays(8),
        'fecha_fin_bloqueo' => now()->subMinute(),
    ])->save();
    $this->artisan('spei:levantar-sanciones')->assertSuccessful();
    expect($e->alumno->fresh()->bloqueado)->toBeFalse()->and($e->alumno->fresh()->marcas_acumuladas)->toBe(0);
});

it('exenta con justificación, resta marcas y levanta el bloqueo; limita al responsable a su área', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    entregarYDevolver($this, $e, $prestamo, 'danado', [12, 30]);
    $danado = Penalizacion::query()->where('tipo_penalizacion', 'danado')->firstOrFail();
    $otraArea = Area::factory()->deInstitucion($e->institucion)->create();
    $responsableAjeno = Usuario::factory()->responsableDe($otraArea)->create();

    $this->actingAs($responsableAjeno)->post(route('sanciones.exentar', $danado), [
        'motivo_exencion' => 'El daño ya existía antes de la entrega según la bitácora.',
    ])->assertNotFound();
    $this->actingAs($e->operador)->get(route('sanciones.index'))->assertOk()->assertSee('Devuelto dañado');
    $this->post(route('sanciones.exentar', $danado), ['motivo_exencion' => 'Corto'])
        ->assertSessionHasErrors('motivo_exencion', null, 'exencion');
    $this->post(route('sanciones.exentar', $danado), [
        'motivo_exencion' => 'El daño ya existía antes de la entrega según la bitácora.',
    ])->assertSessionHas('success');

    expect($danado->fresh()->exentada)->toBeTrue()
        ->and($e->alumno->fresh()->marcas_acumuladas)->toBe(0)
        ->and($e->alumno->fresh()->bloqueado)->toBeFalse()
        ->and(LogAuditoria::query()->where('accion', 'EXENTAR')->exists())->toBeTrue();
});

it('cierra como no devuelto solo un préstamo atrasado y solo el administrador del sistema', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $servicio = app(PrestamoService::class);
    $this->travelTo(today()->setTime(10, 5));
    $servicio->confirmar($e->operador, $prestamo, [
        'codigo' => $servicio->generarToken($e->alumno, $prestamo), 'condicion' => 'bueno',
    ]);
    $motivo = ['motivo' => 'Se contactó al alumno dos veces y no devolvió el equipo.'];
    $asistente = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Asistente)
        ->create(['id_area_asignada' => $e->area->id_area]);

    $this->actingAs($e->operador)->post(route('prestamos.no-devuelto', $prestamo), $motivo)->assertSessionHas('error');
    $this->travelTo(today()->setTime(13, 0));
    $this->actingAs($asistente)->post(route('prestamos.no-devuelto', $prestamo), $motivo)->assertForbidden();
    $this->actingAs($e->operador)->get(route('prestamos.index', ['p' => $prestamo->id_prestamo]))
        ->assertSee('Reportar pérdida');
    $this->post(route('prestamos.no-devuelto', $prestamo), $motivo)->assertSessionHas('success');

    expect($prestamo->fresh()->estado)->toBe('devuelto')
        ->and($prestamo->detalles()->value('condicion_devolucion'))->toBe('no_entregado')
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Mantenimiento)
        ->and(Penalizacion::query()->pluck('tipo_penalizacion')->all())->toBe([TipoIncidente::NoEntregado])
        ->and($e->alumno->fresh()->bloqueado)->toBeTrue();

    $migracionHistorial = require base_path(
        'database/migrations/2026_10_27_000006_create_historial_comprimido_table.php',
    );
    expect(fn (): mixed => $migracionHistorial->down())
        ->toThrow(LogicException::class, 'No se puede revertir el bloque')
        ->and(Schema::hasTable('historial_comprimido'))->toBeTrue();

    $migracionDetalle = require base_path(
        'database/migrations/2026_10_27_000004_add_no_entregado_to_detalle_prestamo.php',
    );
    expect(fn (): mixed => $migracionDetalle->down())
        ->toThrow(LogicException::class, 'No se puede revertir la migración')
        ->and($prestamo->detalles()->value('condicion_devolucion'))->toBe('no_entregado');
});

it('notifica a los administradores del área al recibir una solicitud (RF-049)', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $otraArea = Area::factory()->deInstitucion($e->institucion)->create();
    $administradorDelArea = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::AdminSistema)
        ->create(['id_area_asignada' => $e->area->id_area]);
    $administradorDeOtraArea = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::AdminSistema)
        ->create(['id_area_asignada' => $otraArea->id_area]);

    solicitarComoAlumno($this, $e->alumno, $e->recurso);

    $avisos = Notificacion::query()->where('titulo', 'like', 'Solicitud pendiente%')->get();
    expect($avisos->pluck('id_usuario')->all())->toEqualCanonicalizing([
        $e->operador->id_usuario,
        $administradorDelArea->id_usuario,
    ])->and($avisos->every(
        fn (Notificacion $aviso): bool => $aviso->tipo_notificacion === TipoNotificacion::Push,
    ))->toBeTrue()->and($avisos->contains(
        fn (Notificacion $aviso): bool => $aviso->id_usuario === $administradorDeOtraArea->id_usuario,
    ))->toBeFalse();
});

it('configura la matriz por institución y respeta reglas inactivas', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $ajena = crearEscenario();
    $this->actingAs($e->operador)->get(route('sanciones.matriz'))->assertOk()->assertSee('Devolución tardía');
    $reglas = [];
    foreach (TipoIncidente::cases() as $tipo) {
        $reglas[$tipo->value] = [
            'marcas_por_incidente' => 1,
            'umbral_bloqueo' => 5,
            'dias_sancion' => 3,
            'activo' => 1,
        ];
    }
    $reglas['retraso']['activo'] = 0;
    $this->put(route('sanciones.matriz.update'), ['reglas' => $reglas])->assertRedirect(route('sanciones.matriz'));

    expect(MatrizSancion::query()->where('tipo_incidente', 'danado')->value('umbral_bloqueo'))->toBe(5)
        ->and(MatrizSancion::withoutGlobalScopes()->where('id_institucion', $ajena->institucion->id_institucion)
            ->where('umbral_bloqueo', 5)->exists())->toBeFalse();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    entregarYDevolver($this, $e, $prestamo, 'bueno', [12, 30]);
    expect(Penalizacion::query()->count())->toBe(0);

    $responsable = Usuario::factory()->responsableDe($e->area)->create();
    $this->actingAs($responsable)->get(route('sanciones.matriz'))->assertForbidden();
});

it('envía recordatorios de entrega y devolución una sola vez', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $this->travelTo(today()->setTime(9, 52));
    $this->artisan('spei:enviar-recordatorios')->assertSuccessful();
    $this->artisan('spei:enviar-recordatorios')->assertSuccessful();
    expect(Notificacion::query()->where('titulo', 'Tu préstamo empieza pronto')->count())->toBe(1);

    $servicio = app(PrestamoService::class);
    $this->travelTo(today()->setTime(10, 5));
    $servicio->confirmar($e->operador, $prestamo, [
        'codigo' => $servicio->generarToken($e->alumno, $prestamo), 'condicion' => 'bueno',
    ]);
    $this->travelTo(today()->setTime(11, 55));
    $this->artisan('spei:enviar-recordatorios')->assertSuccessful();
    expect(Notificacion::query()->where('titulo', 'Devuelve tu préstamo')->count())->toBe(1);
    Mail::assertQueued(AvisoMail::class, fn (AvisoMail $correo): bool => $correo->titulo === 'Devuelve tu préstamo');
});

it('abre la ventana de recogida y bloquea el recurso para la siguiente reserva al devolver', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $primero = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $docente = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Docente)->create();
    $this->travel(10)->minutes();
    $segundo = app(PrestamoService::class)->solicitar($docente, [
        'recursos' => [$e->recurso->id_recurso],
        'fecha_hora_inicio' => today()->setTime(12, 0)->toDateTimeString(),
        'fecha_hora_fin' => today()->setTime(13, 0)->toDateTimeString(),
        'motivo' => 'Clase de la tarde.',
    ]);
    expect($segundo->estado)->toBe('pendiente');

    entregarYDevolver($this, $e, $primero, 'bueno', [12, 5]);
    expect($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Bloqueado);
});

it('conversa antes de la entrega entre el solicitante y el mostrador del área', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $otroAlumno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();

    $this->actingAs($e->alumno)->post(route('prestamos.mensajes', $prestamo), ['mensaje' => '¿Incluye el cable HDMI?'])
        ->assertRedirect(route('prestamos.show', $prestamo));
    expect(Notificacion::query()->where('id_usuario', $e->operador->id_usuario)
        ->where('titulo', 'like', 'Mensaje sobre%')->exists())->toBeTrue();
    $this->actingAs($e->operador)->get(route('prestamos.show', $prestamo))->assertOk()
        ->assertSee('¿Incluye el cable HDMI?');
    expect(MensajeConversacion::query()->value('leido'))->toBeTrue();
    $this->post(route('prestamos.mensajes', $prestamo), ['mensaje' => 'Sí, va en el estuche.'])
        ->assertRedirect();
    $this->actingAs($otroAlumno)->post(route('prestamos.mensajes', $prestamo), ['mensaje' => 'Hola'])
        ->assertForbidden();

    $prestamo->forceFill(['estado' => 'cancelado'])->save();
    $this->actingAs($e->alumno)->post(route('prestamos.mensajes', $prestamo), ['mensaje' => 'Otra'])->assertForbidden();
    expect(MensajeConversacion::query()->count())->toBe(2);
});

it('comprime el historial de más de un año y elimina el archivo a los cuatro (RN-012)', function (): void {
    /** @var TestCase $this */
    Storage::fake('local');
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    entregarYDevolver($this, $e, $prestamo, 'danado', [12, 30]);
    $idPenalizacion = Penalizacion::query()->value('id_penalizacion');

    $this->travelTo(now()->addMonths(14));
    $this->artisan('spei:comprimir-historial')->assertSuccessful();
    $historial = HistorialComprimido::withoutGlobalScopes()->firstOrFail();
    expect(Prestamo::withoutGlobalScopes()->count())->toBe(0)
        ->and(Penalizacion::withoutGlobalScopes()->find($idPenalizacion)?->id_prestamo)->toBeNull()
        ->and(gzdecode((string) Storage::disk('local')->get($historial->archivo_comprimido_url)))
        ->toContain($prestamo->motivo);
    $this->artisan('spei:comprimir-historial')->assertSuccessful();
    expect(HistorialComprimido::withoutGlobalScopes()->count())->toBe(1);

    $this->travelTo($historial->fecha_eliminacion_programada->copy()->addDay());
    $this->artisan('spei:comprimir-historial')->assertSuccessful();
    expect($historial->fresh()->eliminado)->toBeTrue();
    Storage::disk('local')->assertMissing($historial->archivo_comprimido_url);
});

it('muestra y exporta horas de uso, sanciones y cancelaciones con su origen', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    entregarYDevolver($this, $e, $prestamo, 'malo', [12, 30]);
    $segundo = Recurso::factory()->deArea($e->area)->create(['nombre_recurso' => 'Tableta Wacom']);
    $this->travelTo(today()->setTime(13, 0));
    $cancelado = app(PrestamoService::class)->solicitar($e->alumno, [
        'recursos' => [$segundo->id_recurso],
        'fecha_hora_inicio' => today()->setTime(14, 0)->toDateTimeString(),
        'fecha_hora_fin' => today()->setTime(15, 0)->toDateTimeString(),
        'motivo' => 'Diseño de carteles.',
    ]);
    $this->actingAs($e->alumno)->post(route('prestamos.cancelar', $cancelado), [
        'motivo_cancelacion' => 'Ya no la necesito.',
    ])->assertSessionHas('success');

    $this->actingAs($e->operador)->get(route('reportes.index', ['periodo' => 7]))->assertOk()
        ->assertSee('Horas de uso por recurso')->assertSee('Proyector Epson')->assertSee('2 h 25 min')
        ->assertSee('Devuelto en mal estado')->assertSee('Ya no la necesito.')->assertSee('Solicitante');
    $csv = $this->get(route('reportes.exportar', ['seccion' => 'cancelaciones', 'periodo' => 7]))->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->streamedContent();
    expect($csv)->toContain($cancelado->obtenerFolio())->toContain('Ya no la necesito.')
        ->and(LogAuditoria::query()->where('accion', 'EXPORTAR_REPORTE')->exists())->toBeTrue();
    $this->get(route('reportes.exportar', ['seccion' => 'otra']))->assertSessionHasErrors('seccion');
    $sanciones = $this->get(route('reportes.exportar', ['seccion' => 'sanciones', 'periodo' => 7]))->streamedContent();
    expect($sanciones)->toContain('"Devuelto en mal estado",1,1,0');
});
