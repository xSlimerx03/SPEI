<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas de interfaz
 * @file         InterfazPrototipoTest.php
 * @path         tests/Feature/InterfazPrototipoTest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pruebas de las pantallas del prototipo hi-fi (DT-028): inicio
 *               por rol, recorrido de solicitud, QR, entrega y devolución en
 *               mostrador, panel de préstamos, cancelación justificada,
 *               auditoría, reportes, recursos, catálogo, notificaciones y
 *               aislamiento entre instituciones.
 *
 * @requisitos   RF-013, RF-015, RF-022, RF-028, RF-029, RF-034, RF-036, RF-052, RF-054, RS-006
 *
 * @see          docs/decisiones/BBB_DT_Registro_v1.0.md (DT-028)
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\EstadoRecurso;
use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Notificacion;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
    // Mañana de un día hábil dentro del horario operativo (7:00 a 22:00).
    $this->travelTo(now()->startOfWeek()->addDays(2)->setTime(9, 0));
});

it('lleva a cada rol a su pantalla de inicio del prototipo', function (Rol $rol, string $texto): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $usuario = Usuario::factory()->deInstitucion($e->institucion)->conRol($rol)->create();

    $this->actingAs($usuario)->followingRedirects()->get(route('panel'))
        ->assertOk()->assertSee($texto)->assertSee('Contraer menú');
})->with([
    'administrador del sistema' => [Rol::AdminSistema, 'Préstamos por día'],
    'administrador institucional' => [Rol::AdminInstitucional, 'Uso del plan'],
    'asistente' => [Rol::Asistente, 'Escanear código QR'],
    'alumno' => [Rol::Alumno, 'Recursos disponibles'],
    'docente' => [Rol::Docente, 'Recursos disponibles'],
]);

it('recorre solicitud, QR, entrega y devolución desde las pantallas', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $this->actingAs($e->alumno)->get(route('prestamos.create', ['recurso' => $e->recurso->id_recurso]))
        ->assertOk()->assertSee('¿Qué recursos necesitas?')->assertSee('Proyector Epson');
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    // DT-033: la reserva futura no bloquea el recurso hasta que empieza su ventana de recogida.
    expect($prestamo->estado)->toBe('pendiente')
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible);

    $this->get(route('prestamos.mis'))->assertOk()->assertSee($prestamo->obtenerFolio())->assertSee('QR de entrega');

    $this->travelTo(today()->setTime(10, 5));
    $this->artisan('spei:activar-reservas')->assertSuccessful();
    expect($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Bloqueado);
    $token = $this->postJson(route('prestamos.token', $prestamo))->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonStructure(['codigo', 'svg', 'expira_en', 'ttl', 'tipo'])->json();
    expect($token['tipo'])->toBe('entrega')->and($token['svg'])->toContain('<svg');

    $this->actingAs($e->operador)->post(route('mostrador.validar'), ['codigo' => $token['codigo']])
        ->assertOk()->assertSee('Código válido · Entrega')->assertSee($e->alumno->nombre_completo);
    $this->post(route('prestamos.confirmar', $prestamo), ['codigo' => $token['codigo'], 'condicion' => 'bueno'])
        ->assertRedirect(route('mostrador.index'))->assertSessionHas('success', 'Entrega confirmada correctamente.');
    expect($prestamo->fresh()->estado)->toBe('activo')
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Prestado);

    $devolucion = $this->actingAs($e->alumno)->postJson(route('prestamos.token', $prestamo))->json();
    expect($devolucion['tipo'])->toBe('devolucion');
    $this->actingAs($e->operador)->post(route('prestamos.confirmar', $prestamo), [
        'codigo' => $devolucion['codigo'], 'condicion' => 'regular',
    ])->assertSessionHas('success', 'Devolución confirmada correctamente.');
    expect($prestamo->fresh()->estado)->toBe('devuelto')
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible);

    $this->get(route('prestamos.show', $prestamo))->assertOk()->assertSee('Comprobante de préstamo')
        ->assertSee('Regular');
});

it('rechaza en mostrador códigos inválidos, reutilizados o de otra institución', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $this->travelTo(today()->setTime(10, 1));
    $codigo = $this->postJson(route('prestamos.token', $prestamo))->json('codigo');

    $ajena = Institucion::factory()->create();
    $operadorAjeno = Usuario::factory()->deInstitucion($ajena)->conRol(Rol::AdminSistema)->create();
    $this->actingAs($operadorAjeno)->post(route('mostrador.validar'), ['codigo' => $codigo])
        ->assertOk()->assertSee('Código rechazado');
    $this->post(route('prestamos.confirmar', $prestamo), ['codigo' => $codigo, 'condicion' => 'bueno'])
        ->assertNotFound();

    $this->actingAs($e->operador)->post(route('mostrador.validar'), ['codigo' => 'no-es-un-codigo'])
        ->assertOk()->assertSee('El código no es válido, ya se utilizó o ha caducado.');
    $this->post(route('prestamos.confirmar', $prestamo), ['codigo' => $codigo, 'condicion' => 'bueno'])
        ->assertRedirect();
    $this->post(route('mostrador.validar'), ['codigo' => $codigo])->assertSee('Código rechazado');
});

it('muestra el panel de préstamos con pestañas y solo selecciona préstamos del ámbito', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $ajena = Institucion::factory()->create();
    $areaAjena = Area::factory()->deInstitucion($ajena)->create();
    $alumnoAjeno = Usuario::factory()->deInstitucion($ajena)->conRol(Rol::Alumno)->create();
    $ajeno = solicitarComoAlumno($this, $alumnoAjeno, Recurso::factory()->deArea($areaAjena)->create());

    $this->actingAs($e->operador)->get(route('prestamos.index', ['p' => $prestamo->id_prestamo]))
        ->assertOk()->assertSee('Pendientes')->assertSee($prestamo->obtenerFolio())
        ->assertSee('Cancelar reserva')->assertSee('Tolerancia de recogida');
    $this->get(route('prestamos.index', ['p' => $ajeno->id_prestamo]))
        ->assertOk()->assertDontSee($alumnoAjeno->nombre_completo);
    $this->get(route('prestamos.index', ['texto' => $prestamo->obtenerFolio()]))->assertOk()
        ->assertSee($e->alumno->nombre_completo);
    $this->get(route('prestamos.show', $ajeno))->assertNotFound();
    $this->actingAs($e->alumno)->get(route('prestamos.index'))->assertForbidden();
});

it('exige justificación de 20 caracteres al operador y de 5 al solicitante', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $prestamo = solicitarComoAlumno($this, $e->alumno, $e->recurso);

    $this->actingAs($e->operador)->from(route('prestamos.index'))->post(route('prestamos.cancelar', $prestamo), [
        'motivo_cancelacion' => 'Evento oficial',
    ])->assertSessionHasErrorsIn('cancelacion', 'motivo_cancelacion');
    expect($prestamo->fresh()->estado)->toBe('pendiente');

    $this->actingAs($e->alumno)->from(route('prestamos.mis'))->post(route('prestamos.cancelar', $prestamo), [
        'motivo_cancelacion' => 'Ya no lo necesito',
    ])->assertRedirect(route('prestamos.mis'))->assertSessionHas('success');
    expect($prestamo->fresh()->estado)->toBe('cancelado')
        ->and($e->recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible);
    $this->get(route('prestamos.mis', ['vista' => 'historial']))->assertOk()->assertSee($prestamo->obtenerFolio());
});

it('valida el asistente de solicitud en el servidor', function (array $cambio, string $campo): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $datos = array_replace([
        'recursos' => [$e->recurso->id_recurso],
        'fecha' => today()->toDateString(),
        'hora_inicio' => '10:00',
        'hora_fin' => '12:00',
        'motivo' => 'Práctica de laboratorio.',
        'acepta_reglamento' => '1',
    ], $cambio);

    $this->actingAs($e->alumno)->from(route('prestamos.create'))->post(route('prestamos.store'), $datos)
        ->assertSessionHasErrors($campo);
    expect(Prestamo::query()->count())->toBe(0);
})->with([
    'sin reglamento' => [['acepta_reglamento' => null], 'acepta_reglamento'],
    'fin antes del inicio' => [['hora_fin' => '09:30'], 'hora_fin'],
    'fuera de horario' => [['hora_inicio' => '06:00'], 'hora_inicio'],
    'horario pasado' => [['hora_inicio' => '08:00'], 'hora_inicio'],
    'sin recursos' => [['recursos' => []], 'recursos'],
]);

it('muestra recursos con chips, lista, detalle y disponibilidad', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $mantenimiento = Recurso::factory()->deArea($e->area)->conEstado(EstadoRecurso::Mantenimiento)
        ->create(['nombre_recurso' => 'Cámara Canon EOS']);

    $this->actingAs($e->operador)->get(route('recursos.index'))->assertOk()
        ->assertSee('Disponibles')->assertSee('Escanear código')->assertSee('Disponible ahora');
    $this->get(route('recursos.index', ['vista' => 'lista', 'estado' => 'mantenimiento']))->assertOk()
        ->assertSee($mantenimiento->nombre_recurso)->assertDontSee('Proyector Epson');
    $this->get(route('recursos.show', $e->recurso))->assertOk()
        ->assertSee('Iniciar mantenimiento')->assertSee('Historial reciente')->assertSee('horario operativo');

    $this->actingAs($e->alumno)->get(route('catalogo.index'))->assertOk()
        ->assertSee('Proyector Epson')->assertSee('Solicitar')->assertDontSee($mantenimiento->nombre_recurso);
    $this->get(route('catalogo.show', $e->recurso->id_recurso))->assertOk()->assertSee('Solicitar préstamo');
    $this->get(route('catalogo.show', $mantenimiento->id_recurso))->assertNotFound();
});

it('muestra auditoría y reportes solo a quien corresponde', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    solicitarComoAlumno($this, $e->alumno, $e->recurso);
    $responsable = Usuario::factory()->responsableDe($e->area)->create();
    $institucional = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::AdminInstitucional)->create();

    $this->actingAs($e->operador)->get(route('auditoria.index'))->assertOk()
        ->assertSee('Registro inalterable')->assertSee('SOLICITAR_PRESTAMO');
    $this->get(route('auditoria.index', ['accion' => 'SOLICITAR_PRESTAMO']))->assertOk()
        ->assertSee('Solicitud registrada');
    $this->get(route('auditoria.exportar'))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $this->get(route('reportes.index', ['periodo' => 7]))->assertOk()
        ->assertSee('Equipos más prestados')->assertSee('Cancelaciones y vencimientos');
    $this->get(route('reportes.index', ['periodo' => 15]))->assertSessionHasErrors('periodo');

    $this->actingAs($institucional)->get(route('auditoria.index'))->assertOk();
    $this->actingAs($responsable)->get(route('auditoria.index'))->assertForbidden();
    $this->actingAs($e->alumno)->get(route('reportes.index'))->assertForbidden();
});

it('marca todas las notificaciones como leídas y no toca las ajenas', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $propia = Notificacion::query()->create([
        'id_institucion' => $e->institucion->id_institucion, 'id_usuario' => $e->alumno->id_usuario,
        'titulo' => 'Préstamo #1', 'mensaje' => 'Mensaje de prueba',
    ]);
    $ajena = Notificacion::query()->create([
        'id_institucion' => $e->institucion->id_institucion, 'id_usuario' => $e->operador->id_usuario,
        'titulo' => 'Solicitud pendiente #1', 'mensaje' => 'Otro mensaje',
    ]);

    $this->actingAs($e->alumno)->get(route('catalogo.index'))->assertSee('Notificaciones, 1 sin leer');
    $this->from(route('notificaciones.index'))->patch(route('notificaciones.leidas'))->assertRedirect();
    expect($propia->fresh()->leida)->toBeTrue()->and($ajena->fresh()->leida)->toBeFalse();
});

it('muestra el banner de solo lectura y bloquea escrituras', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $e->institucion->update(['estado_suscripcion' => EstadoSuscripcion::SoloLectura]);

    $this->actingAs($e->operador)->get(route('recursos.index'))->assertOk()->assertSee('modo solo lectura');
    $this->post(route('areas.store'), ['nombre_area' => 'Nueva'])->assertForbidden();
});

it('busca instituciones por nombre o dominio desde el panel del superadmin', function (): void {
    /** @var TestCase $this */
    $e = crearEscenario();
    $superadmin = Usuario::factory()->superadmin()->create();
    $otra = Institucion::factory()->create(['nombre_institucion' => 'Instituto Ejemplo']);

    $this->actingAs($superadmin)->get(route('panel'))->assertOk()->assertSee('Próximas a vencer');
    $this->get(route('instituciones.index', ['texto' => 'Ejemplo']))->assertOk()
        ->assertSee('Instituto Ejemplo')->assertDontSee($e->institucion->nombre_institucion);
    $this->get(route('instituciones.show', $otra))->assertOk()->assertSee('Renovar periodo');
});
