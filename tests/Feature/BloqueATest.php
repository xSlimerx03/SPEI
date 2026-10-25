<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas del bloque A
 * @file         BloqueATest.php
 * @path         tests/Feature/BloqueATest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-25
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Criterios de aceptación de CU-002 a CU-010: suscripción,
 *               planes, áreas, recursos, importación CSV, tareas programadas,
 *               API REST v1, cierre de sesión por suspensión e inalterabilidad
 *               de la auditoría.
 *
 * @requisitos   RF-003…RF-015, RF-052, RNF-001, RNF-005, RN-010, RN-011, RN-013, RS-006
 *
 * @see          docs/requisitos/BBB_CU002…CU010; docs/iteraciones/A/BBB_PI01_A_v1.0.md
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\EstadoRecurso;
use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Mail\AlertaVencimientoMail;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\LogAuditoria;
use App\Models\Notificacion;
use App\Models\Plan;
use App\Models\Prestamo;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
    $this->travelTo(now()->startOfWeek()->addDays(2)->setTime(9, 0));
});

/**
 * Institución con administrador del sistema sin área asignada.
 *
 * @param  array<string, mixed>  $atributos  Atributos de la institución.
 * @return object{institucion: Institucion, administrador: Usuario}
 */
function crearInstitucionConAdministrador(array $atributos = []): object
{
    $institucion = Institucion::factory()->create($atributos);

    return (object) [
        'institucion' => $institucion,
        'administrador' => Usuario::factory()->deInstitucion($institucion)->conRol(Rol::AdminSistema)->create(),
    ];
}

it('gestiona áreas respetando límite, nombre único y recursos asociados', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador(['limite_areas' => 2]);
    $this->actingAs($e->administrador);

    $this->get(route('areas.create'))->assertOk()->assertSee('Nombre del área');
    $this->post(route('areas.store'), ['nombre_area' => 'Biblioteca'])->assertRedirect(route('areas.index'));
    $this->from(route('areas.create'))->post(route('areas.store'), ['nombre_area' => 'Biblioteca'])
        ->assertSessionHasErrors('nombre_area');
    $this->post(route('areas.store'), ['nombre_area' => 'Laboratorio'])->assertRedirect();
    $this->from(route('areas.create'))->post(route('areas.store'), ['nombre_area' => 'Auditorio'])
        ->assertSessionHas('error', 'El plan contratado no permite más áreas.');

    $area = Area::query()->where('nombre_area', 'Biblioteca')->firstOrFail();
    $this->get(route('areas.index'))->assertOk()->assertSee('Biblioteca')->assertSee('Laboratorio');
    $this->get(route('areas.edit', $area))->assertOk();
    $this->put(route('areas.update', $area), ['nombre_area' => 'Biblioteca central'])->assertRedirect();
    $this->patch(route('areas.activo', $area), ['activo' => 0])->assertRedirect();
    expect($area->fresh()->nombre_area)->toBe('Biblioteca central')->and($area->fresh()->activo)->toBeFalse();

    Recurso::factory()->deArea($area)->create();
    $this->from(route('areas.index'))->delete(route('areas.destroy', $area))
        ->assertSessionHas('error', 'No se puede eliminar un área con recursos.');
    $vacia = Area::query()->where('nombre_area', 'Laboratorio')->firstOrFail();
    $this->delete(route('areas.destroy', $vacia))->assertRedirect(route('areas.index'));
    expect(Area::query()->whereKey($vacia->id_area)->exists())->toBeFalse();
});

it('aísla las áreas entre instituciones y entre administradores', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    $ajena = Area::factory()->create();
    $propia = Area::factory()->deInstitucion($e->institucion)->create();
    $responsable = Usuario::factory()->responsableDe($propia)->create();

    $this->actingAs($e->administrador)->get(route('areas.edit', $ajena))->assertNotFound();
    $this->put(route('areas.update', $ajena), ['nombre_area' => 'Intruso'])->assertNotFound();
    $this->actingAs($responsable)->get(route('areas.index'))->assertForbidden();
    $this->post(route('areas.store'), ['nombre_area' => 'Otra'])->assertForbidden();
});

it('registra, edita y cambia el estado de recursos con sus reglas', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador(['limite_recursos' => 2]);
    $area = Area::factory()->deInstitucion($e->institucion)->create();
    $this->actingAs($e->administrador);

    $datos = ['id_area' => $area->id_area, 'nombre_recurso' => 'Laptop Dell', 'tipo_recurso' => 'equipo_fisico',
        'numero_inventario' => 'INV-001', 'numero_serie' => 'SN-001', 'anio_adquisicion' => 2024];
    $this->get(route('recursos.create', ['codigo' => 'INV-001']))->assertOk()->assertSee('INV-001');
    $this->post(route('recursos.store'), $datos)->assertSessionHasNoErrors()->assertRedirect();
    $this->from(route('recursos.create'))->post(route('recursos.store'), $datos)
        ->assertSessionHasErrors(['numero_inventario', 'numero_serie']);
    $sinSerie = ['numero_serie' => null];
    $this->post(route('recursos.store'), array_replace($datos, ['numero_inventario' => 'INV-002'] + $sinSerie));
    $this->from(route('recursos.create'))
        ->post(route('recursos.store'), array_replace($datos, ['numero_inventario' => 'INV-003'] + $sinSerie))
        ->assertSessionHas('error', 'El plan contratado no permite más recursos.');

    $recurso = Recurso::query()->where('numero_inventario', 'INV-001')->firstOrFail();
    $this->get(route('recursos.edit', $recurso))->assertOk();
    $this->put(route('recursos.update', $recurso), array_replace($datos, ['nombre_recurso' => 'Laptop Dell 5440']))
        ->assertRedirect(route('recursos.show', $recurso));
    $this->patch(route('recursos.estado', $recurso), ['estado' => 'mantenimiento'])->assertRedirect();
    expect($recurso->fresh()->estado)->toBe(EstadoRecurso::Mantenimiento);
    $this->from(route('recursos.show', $recurso))->patch(route('recursos.estado', $recurso), ['estado' => 'prestado'])
        ->assertSessionHas('error', 'No se permite el cambio de estado solicitado.');
    $this->patch(route('recursos.estado', $recurso), ['estado' => 'disponible']);
    $this->patch(route('recursos.activo', $recurso), ['activo' => 0])->assertRedirect();
    expect($recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible)->and($recurso->fresh()->activo)->toBeFalse();

    $recurso->update(['estado' => EstadoRecurso::Bloqueado, 'activo' => true]);
    $this->from(route('recursos.show', $recurso))
        ->patch(route('recursos.estado', $recurso), ['estado' => 'mantenimiento'])
        ->assertSessionHas('error', 'No se puede enviar a mantenimiento un recurso en uso.');
    $this->from(route('recursos.show', $recurso))->patch(route('recursos.activo', $recurso), ['activo' => 0])
        ->assertSessionHas('error', 'No se puede desactivar un recurso en uso.');

    $this->get(route('recursos.buscar', ['codigo' => 'SN-001']))->assertRedirect(route('recursos.show', $recurso));
    $this->get(route('recursos.buscar', ['codigo' => 'NUEVO-9']))
        ->assertRedirect(route('recursos.create', ['codigo' => 'NUEVO-9']));
});

it('limita al responsable de área a los recursos de su área', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    $propia = Area::factory()->deInstitucion($e->institucion)->create();
    $otra = Area::factory()->deInstitucion($e->institucion)->create();
    $ajeno = Recurso::factory()->deArea($otra)->create();
    $responsable = Usuario::factory()->responsableDe($propia)->create();

    $this->actingAs($responsable)->get(route('recursos.show', $ajeno))->assertForbidden();
    $this->post(route('recursos.store'), [
        'id_area' => $otra->id_area,
        'nombre_recurso' => 'X',
        'tipo_recurso' => 'espacio',
        'numero_inventario' => 'R-1',
    ])->assertForbidden();
    $this->get(route('recursos.index'))->assertOk()->assertDontSee($ajeno->numero_inventario);
});

it('importa el catálogo completo o ninguna fila', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    Area::factory()->deInstitucion($e->institucion)->create(['nombre_area' => 'Audiovisual']);
    $this->actingAs($e->administrador);
    $encabezado = "nombre_recurso,numero_inventario,numero_serie,tipo_recurso,marca,anio_adquisicion,nombre_area\n";

    $this->get(route('recursos.importar'))->assertOk()->assertSee('nombre_recurso,numero_inventario');
    $invalido = UploadedFile::fake()->createWithContent('catalogo.csv', $encabezado
        . "Proyector,INV-1,,equipo_fisico,Epson,2024,Audiovisual\nCámara,INV-1,,dron,Canon,2024,Inexistente\n");
    $this->from(route('recursos.importar'))->post(route('recursos.importar.store'), ['archivo' => $invalido])
        ->assertSessionHasErrors('archivo');
    expect(Recurso::query()->count())->toBe(0);

    $valido = UploadedFile::fake()->createWithContent('catalogo.csv', "\xEF\xBB\xBF" . $encabezado
        . "Proyector,INV-1,SN-1,equipo_fisico,Epson,2024,Audiovisual\nSala B2,ESP-2,,espacio,,,audiovisual\n");
    $this->post(route('recursos.importar.store'), ['archivo' => $valido])
        ->assertRedirect(route('recursos.index'))
        ->assertSessionHas('success', 'Se importaron 2 recursos correctamente.');
    expect(Recurso::query()->count())->toBe(2)
        ->and(LogAuditoria::query()->where('accion', 'IMPORTAR')->exists())->toBeTrue();

    $sinEncabezado = UploadedFile::fake()->createWithContent('catalogo.csv', "a,b,c\n1,2,3\n");
    $this->from(route('recursos.importar'))->post(route('recursos.importar.store'), ['archivo' => $sinEncabezado])
        ->assertSessionHasErrors(['archivo' => 'El encabezado del archivo no es válido.']);
});

it('cambia el plan solo si el uso cabe y permite al superadmin renovar y cambiar el estado', function (): void {
    /** @var TestCase $this */
    $basico = Plan::factory()->create();
    Plan::factory()->pro()->create();
    $institucion = Institucion::factory()->conCorteEn(5)->create();
    $administrador = Usuario::factory()->deInstitucion($institucion)->conRol(Rol::AdminInstitucional)->create();
    Area::factory()->count(3)->deInstitucion($institucion)->create();

    $this->actingAs($administrador)->get(route('suscripcion.show'))->assertOk()
        ->assertSee('Tu plan vence en 5 días')->assertSee('Cambiar al plan Pro');
    $this->put(route('suscripcion.plan'), ['plan' => 'pro'])->assertRedirect(route('suscripcion.show'));
    expect($institucion->fresh()->limite_areas)->toBe(10);
    $basico->update(['limite_areas' => 2]);
    $this->from(route('suscripcion.show'))->put(route('suscripcion.plan'), ['plan' => 'basico'])
        ->assertSessionHas('error', 'El plan no admite las áreas o recursos ya registrados.');
    $this->post(route('instituciones.renovar', $institucion))->assertForbidden();

    $superadmin = Usuario::factory()->superadmin()->create();
    $corte = $institucion->fresh()->fecha_corte;
    $this->actingAs($superadmin)->post(route('instituciones.renovar', $institucion))->assertRedirect();
    expect($institucion->fresh()->fecha_corte->toDateString())->toBe($corte->copy()->addDays(30)->toDateString());
    $this->put(route('instituciones.plan', $institucion), ['plan' => 'pro'])->assertRedirect();
    $this->put(route('instituciones.estado', $institucion), ['estado_suscripcion' => 'suspendida'])->assertRedirect();
    $this->from(route('instituciones.show', $institucion))
        ->put(route('instituciones.estado', $institucion), ['estado_suscripcion' => 'solo_lectura'])
        ->assertSessionHas('error', 'No se permite el cambio de estado solicitado.');
    expect($institucion->fresh()->estado_suscripcion)->toBe(EstadoSuscripcion::Suspendida);
});

it('cierra la sesión de una institución suspendida', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    $this->actingAs($e->administrador)->get(route('panel'))->assertOk();
    $e->institucion->update(['estado_suscripcion' => EstadoSuscripcion::Suspendida]);

    $this->get(route('panel'))->assertRedirect(route('login'))
        ->assertSessionHas('error', 'La suscripción de la institución no está disponible.');
    $this->assertGuest();
});

it('edita planes solo como superadmin y propaga los límites', function (): void {
    /** @var TestCase $this */
    $plan = Plan::factory()->create();
    $institucion = Institucion::factory()->create();
    $superadmin = Usuario::factory()->superadmin()->create();
    $datos = ['nombre' => 'Básico', 'monto_mensual' => 1800, 'limite_areas' => 4, 'limite_recursos' => 150,
        'duracion_dias' => 30, 'activo' => '1'];

    $this->actingAs($superadmin)->get(route('planes.index'))->assertOk()->assertSee('Básico');
    $this->get(route('planes.edit', $plan))->assertOk()->assertSee('Límite de áreas');
    $this->put(route('planes.update', $plan), $datos)->assertRedirect(route('planes.index'));
    $this->from(route('planes.edit', $plan))
        ->put(route('planes.update', $plan), array_replace($datos, ['limite_areas' => 0]))
        ->assertSessionHasErrors('limite_areas');
    expect($institucion->fresh()->limite_areas)->toBe(4)
        ->and(LogAuditoria::query()->where('tabla_afectada', 'planes')->whereNull('id_institucion')->exists())
        ->toBeTrue();

    $administrador = Usuario::factory()->deInstitucion($institucion)->conRol(Rol::AdminInstitucional)->create();
    $this->actingAs($administrador)->put(route('planes.update', $plan), $datos)->assertForbidden();
});

it('ejecuta las tareas programadas de vencimiento, alertas y reservas no recogidas', function (): void {
    /** @var TestCase $this */
    Mail::fake();
    $vencida = Institucion::factory()->conCorteEn(-1)->create();
    $porVencer = Institucion::factory()->conCorteEn(3)->create();
    Usuario::factory()->deInstitucion($porVencer)->conRol(Rol::AdminInstitucional)->create();

    $this->artisan('spei:aplicar-vencimientos')->assertSuccessful();
    $this->artisan('spei:alertar-vencimientos')->assertSuccessful();
    $this->artisan('spei:alertar-vencimientos')->assertSuccessful();
    expect($vencida->fresh()->estado_suscripcion)->toBe(EstadoSuscripcion::SoloLectura)
        ->and(Notificacion::query()->where('id_institucion', $porVencer->id_institucion)->count())->toBe(1);
    Mail::assertQueued(AlertaVencimientoMail::class, 1);

    $area = Area::factory()->create();
    $recurso = Recurso::factory()->deArea($area)->create();
    $alumno = Usuario::factory()->deInstitucion(Institucion::query()->findOrFail($area->id_institucion))
        ->conRol(Rol::Alumno)->create();
    $this->actingAs($alumno)->post(route('prestamos.store'), [
        'recursos' => [$recurso->id_recurso], 'fecha' => today()->toDateString(), 'hora_inicio' => '10:00',
        'hora_fin' => '11:00', 'motivo' => 'Práctica de redes.', 'acepta_reglamento' => '1',
    ])->assertSessionHasNoErrors();
    $this->travelTo(today()->setTime(10, 30));
    $this->artisan('spei:vencer-prestamos')->assertSuccessful();
    expect(Prestamo::query()->sole()->estado)->toBe('vencido')
        ->and($recurso->fresh()->estado)->toBe(EstadoRecurso::Disponible);
});

it('atiende la API v1 con token Sanctum y respeta rol e institución', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    $area = Area::factory()->deInstitucion($e->institucion)->create();
    $recurso = Recurso::factory()->deArea($area)->create(['numero_inventario' => 'INV-API']);
    Recurso::factory()->create(['numero_inventario' => 'INV-AJENO']);
    $alumno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();

    $token = $this->postJson('/api/v1/sesiones', [
        'correo_institucional' => $e->administrador->correo_institucional,
        'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertCreated()->assertJsonPath('tipo', 'Bearer')->json('token');

    $this->withToken($token)->getJson('/api/v1/recursos')->assertOk()
        ->assertJsonPath('data.0.numero_inventario', 'INV-API');
    $this->withToken($token)->getJson('/api/v1/recursos/codigo/INV-API')->assertOk()
        ->assertJsonPath('data.id_recurso', $recurso->id_recurso);
    $this->withToken($token)->getJson('/api/v1/recursos/codigo/INV-AJENO')->assertNotFound();
    $this->withToken($token)->getJson('/api/v1/catalogo')->assertForbidden();
    $this->withToken($token)->deleteJson('/api/v1/sesiones/actual')->assertNoContent();

    $tokenAlumno = $alumno->createToken('pruebas')->plainTextToken;
    $this->app['auth']->forgetGuards();
    $this->withToken($tokenAlumno)->getJson('/api/v1/catalogo')->assertOk()->assertJsonCount(1, 'data');
    $this->postJson('/api/v1/sesiones', ['correo_institucional' => 'nadie@x.mx', 'contrasena' => 'Mala2026'])
        ->assertUnprocessable();
});

it('lista y marca notificaciones propias sin acceder a las ajenas', function (): void {
    /** @var TestCase $this */
    $e = crearInstitucionConAdministrador();
    $otro = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();
    $datos = ['id_institucion' => $e->institucion->id_institucion, 'titulo' => 'Aviso', 'mensaje' => 'Contenido'];
    $propia = Notificacion::query()->create($datos + ['id_usuario' => $e->administrador->id_usuario]);
    $ajena = Notificacion::query()->create($datos + ['id_usuario' => $otro->id_usuario]);

    $this->actingAs($e->administrador)->get(route('notificaciones.index'))->assertOk()->assertSee('Marcar leída');
    $this->patch(route('notificaciones.leida', $propia))->assertRedirect();
    $this->patch(route('notificaciones.leida', $ajena))->assertNotFound();
    expect($propia->fresh()->leida)->toBeTrue()->and($ajena->fresh()->leida)->toBeFalse();
});

it('impide modificar o borrar la bitácora de auditoría', function (): void {
    $e = crearInstitucionConAdministrador();
    $log = app(AuditoriaService::class)->registrar('CREAR', $e->institucion, null, null, $e->administrador);

    expect(fn () => $log->update(['accion' => 'OTRA']))->toThrow(QueryException::class)
        ->and(fn () => $log->delete())->toThrow(QueryException::class);
});
