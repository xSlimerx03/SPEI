<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas de usuarios y roles
 * @file         UsuariosTest.php
 * @path         tests/Feature/UsuariosTest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Pruebas del bloque de usuarios: reglas dominio → rol, alta
 *               por invitación, CSV todo o nada, cambio de rol y delegación,
 *               baja y reactivación, responsables de área únicos, inicio de
 *               sesión con subdominio y aislamiento entre instituciones.
 *
 * @requisitos   RF-016, RF-017, RF-018, RF-019, RF-020, RF-021, RN-001, RS-006
 *
 * @see          CU-016, CU-017, CU-018; DT-034, DT-035
 * ============================================================================
 */

declare(strict_types=1);

use App\Enums\Rol;
use App\Mail\InvitacionAdministradorMail;
use App\Models\Area;
use App\Models\DominioRol;
use App\Models\Institucion;
use App\Models\LogAuditoria;
use App\Models\Recurso;
use App\Models\Usuario;
use App\Services\InvitacionService;
use Database\Factories\UsuarioFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    config(['app.debug' => false]);
    Mail::fake();
});

/**
 * Institución con un área, un administrador del sistema sin área y su dominio.
 *
 * @return object{institucion: Institucion, area: Area, admin: Usuario}
 */
function escenarioUsuarios(): object
{
    $institucion = Institucion::factory()->create(['limite_recursos' => 50]);

    return (object) [
        'institucion' => $institucion,
        'area' => Area::factory()->deInstitucion($institucion)->create(['nombre_area' => 'Laboratorio']),
        'admin' => Usuario::factory()->deInstitucion($institucion)->conRol(Rol::AdminSistema)->create(),
    ];
}

/**
 * Archivo CSV temporal con el contenido indicado.
 */
function csvUsuarios(string $contenido): UploadedFile
{
    return UploadedFile::fake()->createWithContent('usuarios.csv', $contenido);
}

it('registra reglas de dominio y asigna el rol del subdominio más específico', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $dominio = $e->institucion->dominio_correo;
    $this->actingAs($e->admin)->post(route('dominios.store'), ['dominio' => $dominio, 'rol' => 'docente'])
        ->assertRedirect(route('dominios.index'));
    $this->post(route('dominios.store'), ['dominio' => '@ALUMNOS.' . $dominio, 'rol' => 'alumno'])
        ->assertSessionHasNoErrors();
    $this->post(route('dominios.store'), ['dominio' => 'otra-escuela.mx', 'rol' => 'alumno'])
        ->assertSessionHas('error');
    $this->post(route('dominios.store'), ['dominio' => $dominio, 'rol' => 'asistente'])->assertSessionHasErrors('rol');

    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Ana Alumna', 'correo_institucional' => 'ana@alumnos.' . $dominio,
    ])->assertRedirect(route('usuarios.index'));
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Daniel Docente', 'correo_institucional' => 'daniel@' . $dominio,
    ])->assertRedirect(route('usuarios.index'));

    expect(Usuario::query()->where('correo_institucional', 'ana@alumnos.' . $dominio)->value('rol'))->toBe(Rol::Alumno)
        ->and(Usuario::query()->where('correo_institucional', 'daniel@' . $dominio)->value('rol'))->toBe(Rol::Docente)
        ->and(DominioRol::query()->count())->toBe(2);
    Mail::assertQueued(InvitacionAdministradorMail::class, 2);
});

it('exige un rol explícito sin regla de dominio y rechaza correos ajenos al dominio', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $dominio = $e->institucion->dominio_correo;
    $this->actingAs($e->admin)->post(route('usuarios.store'), [
        'nombre_completo' => 'Sin Regla', 'correo_institucional' => 'sinregla@' . $dominio,
    ])->assertSessionHasErrors('correo_institucional');
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Ajeno', 'correo_institucional' => 'ajeno@gmail.com', 'rol' => 'alumno',
    ])->assertSessionHasErrors('correo_institucional');
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Asistente Sin Area', 'correo_institucional' => 'asis@' . $dominio, 'rol' => 'asistente',
    ])->assertSessionHasErrors('correo_institucional');
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Asistente', 'correo_institucional' => 'asis@' . $dominio, 'rol' => 'asistente',
        'id_area_asignada' => $e->area->id_area,
    ])->assertRedirect(route('usuarios.index'));

    $asistente = Usuario::query()->where('correo_institucional', 'asis@' . $dominio)->firstOrFail();
    expect($asistente->rol)->toBe(Rol::Asistente)
        ->and($asistente->id_area_asignada)->toBe($e->area->id_area)
        ->and($asistente->activo)->toBeFalse()
        ->and($asistente->obtenerEstadoCuenta())->toBe('pendiente')
        ->and(Usuario::query()->where('correo_institucional', 'like', 'sinregla%')->exists())->toBeFalse();
});

it('importa usuarios por CSV completo o ninguno', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $dominio = $e->institucion->dominio_correo;
    DominioRol::query()->create(['id_institucion' => $e->institucion->id_institucion, 'dominio' => $dominio,
        'rol' => Rol::Alumno]);
    $encabezado = "correo_institucional,nombre_completo,rol,nombre_area\n";
    $invalido = $encabezado . "uno@{$dominio},Uno,,\ndos@{$dominio},Dos,asistente,Inexistente\n"
        . "uno@{$dominio},Repetido,,\ntres@gmail.com,Tres,docente,\n";

    $this->actingAs($e->admin)->post(route('usuarios.importar.store'), ['archivo' => csvUsuarios($invalido)])
        ->assertSessionHasErrors('archivo');
    $errores = session('errors')->get('archivo');
    expect($errores)->toHaveCount(3)
        ->and(Usuario::query()->where('rol', Rol::Alumno)->count())->toBe(0);

    $valido = "\xEF\xBB\xBF" . $encabezado . "uno@{$dominio},Uno,,\ndos@{$dominio},Dos,docente,\n"
        . "tres@{$dominio},Tres,asistente,laboratorio\n";
    $this->post(route('usuarios.importar.store'), ['archivo' => csvUsuarios($valido)])
        ->assertRedirect(route('usuarios.index'))->assertSessionHas('success');

    expect(Usuario::query()->whereIn('rol', ['alumno', 'docente', 'asistente'])->count())->toBe(3)
        ->and(Usuario::query()->where('correo_institucional', "tres@{$dominio}")->value('id_area_asignada'))
        ->toBe($e->area->id_area)
        ->and(LogAuditoria::query()->where('accion', 'IMPORTAR')->where('tabla_afectada', 'usuarios')->exists())
        ->toBeTrue();
    Mail::assertQueued(InvitacionAdministradorMail::class, 3);
    $this->post(route('usuarios.importar.store'), ['archivo' => csvUsuarios("correo,nombre\n")])
        ->assertSessionHasErrors('archivo');
});

it('cambia el rol, delega asistentes con auditoría y da de baja sin préstamos abiertos', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $otraArea = Area::factory()->deInstitucion($e->institucion)->create(['nombre_area' => 'Biblioteca']);
    $alumno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();

    $this->actingAs($e->admin)->put(route('usuarios.update', $alumno), ['rol' => 'docente'])
        ->assertRedirect(route('usuarios.index'));
    expect($alumno->fresh()->rol)->toBe(Rol::Docente);
    $this->put(route('usuarios.update', $alumno), ['rol' => 'asistente', 'id_area_asignada' => $otraArea->id_area]);
    expect($alumno->fresh()->rol)->toBe(Rol::Asistente)
        ->and(LogAuditoria::query()->where('accion', 'DELEGAR')->where('registro_id', $alumno->id_usuario)->exists())
        ->toBeTrue();
    $this->put(route('usuarios.update', $alumno), ['rol' => 'admin_sistema'])->assertSessionHasErrors('rol');

    $this->patch(route('usuarios.activo', $alumno), ['activo' => 0])->assertSessionHas('success');
    expect($alumno->fresh()->obtenerEstadoCuenta())->toBe('baja');
    $this->post(route('sesion.destroy'));
    $this->post(route('sesion.store'), [
        'correo_institucional' => $alumno->correo_institucional, 'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertSessionHasErrors('correo_institucional');
    $this->actingAs($e->admin)->patch(route('usuarios.activo', $alumno), ['activo' => 1]);
    expect($alumno->fresh()->activo)->toBeTrue();

    $alumnoConPrestamo = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();
    $recurso = Recurso::factory()->deArea($e->area)->create();
    $this->travelTo(now()->startOfWeek()->addDays(2)->setTime(9, 0));
    solicitarComoAlumno($this, $alumnoConPrestamo, $recurso);
    $this->actingAs($e->admin)->patch(route('usuarios.activo', $alumnoConPrestamo), ['activo' => 0])
        ->assertSessionHas('error');
    expect($alumnoConPrestamo->fresh()->activo)->toBeTrue();
});

it('limita al responsable de área a los asistentes de su área', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $responsable = Usuario::factory()->responsableDe($e->area)->create();
    $otraArea = Area::factory()->deInstitucion($e->institucion)->create();
    $alumno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Alumno)->create();
    $asistenteAjeno = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Asistente)
        ->create(['id_area_asignada' => $otraArea->id_area]);
    $dominio = $e->institucion->dominio_correo;

    $this->actingAs($responsable)->get(route('usuarios.index'))->assertOk()->assertSee('Asistentes de tu área')
        ->assertDontSee($alumno->correo_institucional)->assertDontSee($asistenteAjeno->correo_institucional);
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Nuevo Asistente', 'correo_institucional' => 'nuevo@' . $dominio, 'rol' => 'asistente',
        'id_area_asignada' => $otraArea->id_area,
    ])->assertRedirect(route('usuarios.index'));
    expect(Usuario::query()->where('correo_institucional', 'nuevo@' . $dominio)->value('id_area_asignada'))
        ->toBe($e->area->id_area);
    $this->post(route('usuarios.store'), [
        'nombre_completo' => 'Alumno', 'correo_institucional' => 'al@' . $dominio, 'rol' => 'alumno',
    ])->assertSessionHasErrors('correo_institucional');
    $this->get(route('usuarios.edit', $alumno))->assertNotFound();
    $this->get(route('usuarios.edit', $asistenteAjeno))->assertNotFound();
    $this->get(route('dominios.index'))->assertForbidden();
});

it('aísla usuarios y reglas de dominio entre instituciones', function (): void {
    /** @var TestCase $this */
    $propia = escenarioUsuarios();
    $ajena = escenarioUsuarios();
    $alumnoAjeno = Usuario::factory()->deInstitucion($ajena->institucion)->conRol(Rol::Alumno)->create();
    $reglaAjena = DominioRol::query()->create(['id_institucion' => $ajena->institucion->id_institucion,
        'dominio' => $ajena->institucion->dominio_correo, 'rol' => Rol::Alumno]);

    $this->actingAs($propia->admin)->get(route('usuarios.index'))->assertOk()
        ->assertDontSee($alumnoAjeno->correo_institucional);
    $this->get(route('usuarios.edit', $alumnoAjeno))->assertNotFound();
    $this->patch(route('usuarios.activo', $alumnoAjeno), ['activo' => 0])->assertNotFound();
    $this->delete(route('dominios.destroy', $reglaAjena))->assertNotFound();
    $this->get(route('dominios.index'))->assertOk()->assertDontSee($ajena->institucion->dominio_correo);
    $this->actingAs($alumnoAjeno)->get(route('usuarios.index'))->assertForbidden();
});

it('mantiene un responsable por área y permite cambiar su área o darlo de baja', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $institucional = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::AdminInstitucional)->create();
    $responsable = Usuario::factory()->responsableDe($e->area)->create();
    $otro = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::AdminSistema)->create();

    $this->actingAs($institucional)->post(route('administradores.store'), [
        'nombre_completo' => 'Segundo Responsable',
        'correo_institucional' => 'segundo@' . $e->institucion->dominio_correo,
        'id_area_asignada' => $e->area->id_area,
    ])->assertSessionHas('error');
    $this->put(route('administradores.update', $otro), ['id_area_asignada' => $e->area->id_area])
        ->assertSessionHas('error');
    $this->patch(route('administradores.activo', $responsable), ['activo' => 0])->assertSessionHas('success');
    $this->put(route('administradores.update', $otro), ['id_area_asignada' => $e->area->id_area])
        ->assertRedirect(route('administradores.index'));
    expect($otro->fresh()->id_area_asignada)->toBe($e->area->id_area);
    $this->patch(route('administradores.activo', $responsable), ['activo' => 1])->assertSessionHas('error');
    $this->patch(route('administradores.activo', $e->admin), ['activo' => 0])->assertSessionHas('error');
    $this->get(route('administradores.index'))->assertOk()->assertSee('Dada de baja');
});

it('inicia sesión con un correo de subdominio y rechaza dominios externos', function (): void {
    /** @var TestCase $this */
    $institucion = Institucion::factory()->create();
    $alumno = Usuario::factory()->deInstitucion($institucion)->conRol(Rol::Alumno)
        ->create(['correo_institucional' => 'sub@alumnos.' . $institucion->dominio_correo]);
    $falso = Usuario::factory()->deInstitucion($institucion)->conRol(Rol::Alumno)
        ->create(['correo_institucional' => 'falso@x' . $institucion->dominio_correo]);

    $this->post(route('sesion.store'), [
        'correo_institucional' => $alumno->correo_institucional, 'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertRedirect(route('panel'));
    $this->post(route('sesion.destroy'));
    $this->post(route('sesion.store'), [
        'correo_institucional' => $falso->correo_institucional, 'contrasena' => UsuarioFactory::CONTRASENA_PRUEBA,
    ])->assertSessionHasErrors('correo_institucional');
});

it('impide reactivar con una invitación vieja una cuenta dada de baja', function (): void {
    /** @var TestCase $this */
    $e = escenarioUsuarios();
    $invitado = Usuario::factory()->deInstitucion($e->institucion)->conRol(Rol::Docente)->inactivo()->create();
    $enlace = app(InvitacionService::class)->generarEnlace($invitado);
    $datos = ['contrasena' => 'NuevaClave2026', 'contrasena_confirmation' => 'NuevaClave2026'];
    $this->post($enlace, $datos)->assertRedirect(route('login'));
    expect($invitado->fresh()->completoRegistro())->toBeTrue();

    $this->actingAs($e->admin)->patch(route('usuarios.activo', $invitado), ['activo' => 0]);
    $this->post(route('sesion.destroy'));
    $this->get($enlace)->assertRedirect(route('login'))->assertSessionHas('error');
    $this->post($enlace, ['contrasena' => 'OtraClave2026', 'contrasena_confirmation' => 'OtraClave2026']);
    expect($invitado->fresh()->activo)->toBeFalse();
});
