<?php

/**
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Autenticación y contratación
 * @file         PantallasPublicasTest.php
 * @path         tests/Feature/PantallasPublicasTest.php
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 * @license      Propietario - BigBitByte / Institución Educativa
 * @description  Regresión HTTP de las pantallas públicas y sus límites de seguridad.
 * @requisitos   RF-001, RF-002, RF-008, RNF-001, RS-006
 * @see          docs/requisitos/BBB_CU005_IniciarSesion_v1.0.md
 */

declare(strict_types=1);

use App\Enums\EstadoSuscripcion;
use App\Enums\Rol;
use App\Exceptions\ReglaNegocioException;
use App\Mail\AlertaVencimientoMail;
use App\Mail\InvitacionAdministradorMail;
use App\Models\Institucion;
use App\Models\LogAuditoria;
use App\Models\Plan;
use App\Models\Usuario;
use App\Services\AdministradorService;
use Database\Factories\UsuarioFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
});

it('muestra el acceso con errores asociados a los campos', function (): void {
    /** @var TestCase $this */
    $this->from(route('login'))->post(route('sesion.store'), [
        'correo_institucional' => 'invalido',
    ])->assertSessionHasErrors(['correo_institucional', 'contrasena']);

    $this->get(route('login'))->assertOk()
        ->assertSee('correo_institucional-error', false)
        ->assertSee('contrasena-error', false)
        ->assertSee('El campo contraseña es obligatorio.');
});

it('muestra solo planes activos y conserva su selección tras un error', function (): void {
    /** @var TestCase $this */
    Plan::factory()->create();
    Plan::factory()->pro()->create();
    Plan::factory()->enterprise()->create(['activo' => false]);

    $this->get(route('contratacion.create'))->assertOk()->assertSee('Básico')->assertSee('Pro')
        ->assertDontSee('Enterprise');
    $this->from(route('contratacion.create'))->post(route('contratacion.store'), ['plan' => 'pro'])
        ->assertSessionHasErrors('nombre_institucion');
    $this->get(route('contratacion.create'))->assertOk()
        ->assertSee('value="pro"', false)->assertSee('checked', false);
});

it('deshabilita la contratación cuando no hay planes disponibles', function (): void {
    /** @var TestCase $this */
    $this->get(route('contratacion.create'))->assertOk()
        ->assertSee('No hay planes disponibles')->assertSee('disabled', false);
    $this->post(route('contratacion.store'), ['plan' => 'basico'])->assertSessionHasErrors('plan');
    $this->assertDatabaseCount('instituciones', 0);
});

it('contrata con límites del plan y contraseña exacta sin aceptar un rol enviado', function (): void {
    /** @var TestCase $this */
    $plan = Plan::factory()->create();
    $contrasena = '  ClaveSegura2026  ';

    $this->post(route('contratacion.store'), [
        'plan' => $plan->clave->value,
        'nombre_institucion' => 'Universidad de Prueba',
        'dominio_correo' => ' @PRUEBA.EDU.MX ',
        'nombre_completo' => 'Administrador de Prueba',
        'correo_institucional' => ' ADMIN@PRUEBA.EDU.MX ',
        'contrasena' => $contrasena,
        'contrasena_confirmation' => $contrasena,
        'rol' => 'superadmin',
    ])->assertSessionHasNoErrors()->assertRedirect(route('suscripcion.show'));

    $usuario = Usuario::query()->sole();
    $institucion = Institucion::query()->sole();
    $this->assertAuthenticatedAs($usuario);
    expect($usuario->rol)->toBe(Rol::AdminInstitucional)
        ->and(Hash::check($contrasena, $usuario->contrasena_hash))->toBeTrue()
        ->and($institucion->limite_areas)->toBe($plan->limite_areas)
        ->and($institucion->limite_recursos)->toBe($plan->limite_recursos)
        ->and($institucion->fecha_corte->toDateString())->toBe(today()->addDays(30)->toDateString())
        ->and(LogAuditoria::query()->where('accion', 'CREAR')->count())->toBe(2);
    $this->get(route('suscripcion.show'))->assertOk();
});

it('rechaza datos de contratación sin crear registros ni conservar contraseñas', function (array $cambio, string $campo): void {
    /** @var TestCase $this */
    Plan::factory()->create();
    $datos = array_replace([
        'plan' => 'basico',
        'nombre_institucion' => 'Universidad de Prueba',
        'dominio_correo' => 'prueba.edu.mx',
        'nombre_completo' => 'Administrador de Prueba',
        'correo_institucional' => 'admin@prueba.edu.mx',
        'contrasena' => 'ClaveSegura2026',
        'contrasena_confirmation' => 'ClaveSegura2026',
    ], $cambio);

    $this->from(route('contratacion.create'))->post(route('contratacion.store'), $datos)
        ->assertSessionHasErrors($campo)
        ->assertSessionMissing('_old_input.contrasena')
        ->assertSessionMissing('_old_input.contrasena_confirmation');
    $this->assertDatabaseCount('instituciones', 0);
    $this->assertDatabaseCount('usuarios', 0);
})->with([
    'correo ajeno' => [['correo_institucional' => 'admin@ajeno.edu.mx'], 'correo_institucional'],
    'plan inexistente' => [['plan' => 'desconocido'], 'plan'],
    'contraseña corta' => [['contrasena' => 'Ab1', 'contrasena_confirmation' => 'Ab1'], 'contrasena'],
    'confirmación distinta' => [['contrasena_confirmation' => 'OtraClave2026'], 'contrasena'],
]);

it('inicia sesión y muestra el destino de cada rol', function (Rol $rol): void {
    /** @var TestCase $this */
    $usuario = $rol === Rol::Superadmin
        ? Usuario::factory()->superadmin()->create()
        : Usuario::factory()->conRol($rol)->create();

    $this->post(route('sesion.store'), [
        'correo_institucional' => $usuario->correo_institucional,
        'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertRedirect(route('panel'));
    $this->assertAuthenticatedAs($usuario);
    $this->followingRedirects()->get(route('panel'))->assertOk();
    $this->assertDatabaseHas('logs_auditoria', ['accion' => 'INICIAR_SESION', 'id_usuario' => $usuario->id_usuario]);
    $this->post(route('sesion.destroy'))->assertRedirect(route('login'));
    $this->assertGuest();
})->with(array_map(fn (Rol $rol): array => [$rol], Rol::cases()));

it('rechaza credenciales sin guardar la contraseña en la sesión', function (): void {
    /** @var TestCase $this */
    $this->from(route('login'))->post(route('sesion.store'), [
        'correo_institucional' => 'nadie@prueba.edu.mx',
        'contrasena' => 'ClavePrivada2026',
    ])->assertSessionHasErrors('correo_institucional')
        ->assertSessionMissing('_old_input.contrasena');
    $this->assertGuest();
});

it('rechaza cuentas inactivas y suscripciones suspendidas', function (bool $inactivo): void {
    /** @var TestCase $this */
    $institucion = Institucion::factory()->conEstado(
        $inactivo ? EstadoSuscripcion::Activa : EstadoSuscripcion::Suspendida,
    )->create();
    $usuario = Usuario::factory()->deInstitucion($institucion)->create(['activo' => ! $inactivo]);

    $this->post(route('sesion.store'), [
        'correo_institucional' => $usuario->correo_institucional,
        'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertSessionHasErrors('correo_institucional');
    $this->assertGuest();
})->with([[true], [false]]);

it('limita el sexto intento aunque cambien espacios y mayúsculas del correo', function (): void {
    /** @var TestCase $this */
    for ($intento = 0; $intento < 5; $intento++) {
        $this->post(route('sesion.store'), [
            'correo_institucional' => 'nadie@prueba.edu.mx', 'contrasena' => 'Incorrecta2026',
        ])->assertRedirect();
    }

    $this->post(route('sesion.store'), [
        'correo_institucional' => ' NADIE@PRUEBA.EDU.MX ', 'contrasena' => 'Incorrecta2026',
    ])->assertStatus(429)->assertHeader('Retry-After')->assertSee('Demasiados intentos');
});

it('completa una invitación válida y rechaza su reutilización', function (): void {
    /** @var TestCase $this */
    $usuario = Usuario::factory()->conRol(Rol::AdminSistema)->inactivo()->create();
    $enlace = URL::temporarySignedRoute('registro.completar', now()->addHours(72), ['usuario' => $usuario]);
    $this->get($enlace)->assertOk()->assertSee($usuario->correo_institucional);
    $datos = ['contrasena' => 'NuevaClave2026', 'contrasena_confirmation' => 'NuevaClave2026'];
    $this->post($enlace, $datos)->assertRedirect(route('login'));
    expect($usuario->fresh()->activo)->toBeTrue()
        ->and(Hash::check('NuevaClave2026', $usuario->fresh()->contrasena_hash))->toBeTrue();
    $this->get($enlace)->assertRedirect(route('login'))->assertSessionHas('error');
    $this->post($enlace, ['contrasena' => 'OtraClave2026', 'contrasena_confirmation' => 'OtraClave2026'])
        ->assertSessionHas('error')->assertSessionMissing('_old_input.contrasena');
    expect(Hash::check('NuevaClave2026', $usuario->fresh()->contrasena_hash))->toBeTrue()
        ->and(LogAuditoria::query()->where('accion', 'COMPLETAR_REGISTRO')->count())->toBe(1);
});

it('rechaza enlaces de invitación caducados o alterados sin activar al usuario', function (bool $caducado): void {
    /** @var TestCase $this */
    $usuario = Usuario::factory()->conRol(Rol::AdminSistema)->inactivo()->create();
    $enlace = URL::temporarySignedRoute('registro.completar',
        $caducado ? now()->subMinute() : now()->addHours(72), ['usuario' => $usuario]);
    $enlace .= $caducado ? '' : '&alterado=1';
    $this->get($enlace)->assertForbidden()->assertSee('El enlace no es válido o ha caducado.');
    $this->post($enlace, ['contrasena' => 'NuevaClave2026', 'contrasena_confirmation' => 'NuevaClave2026'])
        ->assertForbidden();
    expect($usuario->fresh()->activo)->toBeFalse();
})->with([[true], [false]]);

it('rechaza una segunda activación con un modelo cargado antes de la primera', function (): void {
    /** @var TestCase $this */
    $usuario = Usuario::factory()->conRol(Rol::AdminSistema)->inactivo()->create();
    $copiaAnterior = $usuario->fresh();
    $servicio = app(AdministradorService::class);
    $servicio->completarRegistro($usuario, 'PrimeraClave2026');
    expect(fn () => $servicio->completarRegistro($copiaAnterior, 'SegundaClave2026'))
        ->toThrow(ReglaNegocioException::class, 'El registro ya fue completado.');
    expect(Hash::check('PrimeraClave2026', $usuario->fresh()->contrasena_hash))->toBeTrue();
});

it('genera la invitación desde el formulario y permite abrir el enlace del correo', function (): void {
    /** @var TestCase $this */
    Mail::fake();
    $administrador = Usuario::factory()->conRol(Rol::AdminInstitucional)->create();
    $institucion = Institucion::query()->findOrFail($administrador->id_institucion);
    $this->actingAs($administrador)->post(route('administradores.store'), [
        'nombre_completo' => 'Responsable de Prueba',
        'correo_institucional' => 'responsable@' . $institucion->dominio_correo,
    ])->assertSessionHasNoErrors()->assertRedirect();
    $correo = Mail::queued(InvitacionAdministradorMail::class)->sole();
    expect($correo->render())->toContain('Completar registro', 'Responsable de Prueba');
    $this->post(route('sesion.destroy'));
    $this->get($correo->enlace)->assertOk()->assertSee('Completar registro');
});

it('renderiza los correos con datos escapados sin enviar mensajes reales', function (): void {
    $invitacion = new InvitacionAdministradorMail('<script>nombre</script>', 'Institución', 'https://example.test/registro');
    expect($invitacion->render())->toContain('&lt;script&gt;nombre&lt;/script&gt;');
    expect(str_contains($invitacion->render(), '<script>'))->toBeFalse();
    $alerta = new AlertaVencimientoMail('Administrador', 'Su suscripción vence en tres días.');
    expect($alerta->render())->toContain('Su suscripción vence en tres días.', 'Ver suscripción');
});

it('conserva el enlace firmado tras una contraseña inválida sin guardarla en sesión', function (): void {
    /** @var TestCase $this */
    $usuario = Usuario::factory()->conRol(Rol::AdminSistema)->inactivo()->create();
    $enlace = URL::temporarySignedRoute('registro.completar', now()->addHours(72), ['usuario' => $usuario]);
    $this->from($enlace)->post($enlace, ['contrasena' => 'Corta1', 'contrasena_confirmation' => 'Corta1'])
        ->assertRedirect($enlace)->assertSessionHasErrors('contrasena')
        ->assertSessionMissing('_old_input.contrasena')->assertSessionMissing('_old_input.contrasena_confirmation');
    $this->get($enlace)->assertOk()->assertSee(e($enlace), false);
    expect($usuario->fresh()->activo)->toBeFalse();
});
