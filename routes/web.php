<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Rutas web
 * @file         web.php
 * @path         routes/web.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-23
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Rutas de las vistas Blade: bloque A, incremento MVP de
 *               préstamos y pantallas del prototipo hi-fi (mostrador,
 *               auditoría, reportes y detalle de recurso). Rutas en kebab-case
 *               (MP 1.5); el acceso se controla por autenticación, estado de la
 *               suscripción, rol y Policies.
 *
 * @requisitos   RF-001…RF-015, RF-022…RF-028, RF-034, RF-036, RF-051…RF-055, RNF-001, RS-006
 *
 * @see          docs/iteraciones/A/BBB_PI01_A_v1.0.md; docs/plan/BBB_MVP_v1.0.md; DT-028
 * ============================================================================
 */

declare(strict_types=1);

use App\Http\Controllers\Web\AdministradorController;
use App\Http\Controllers\Web\AreaController;
use App\Http\Controllers\Web\AuditoriaController;
use App\Http\Controllers\Web\CatalogoController;
use App\Http\Controllers\Web\ContratacionController;
use App\Http\Controllers\Web\ImportacionController;
use App\Http\Controllers\Web\InstitucionController;
use App\Http\Controllers\Web\MostradorController;
use App\Http\Controllers\Web\NotificacionController;
use App\Http\Controllers\Web\PanelController;
use App\Http\Controllers\Web\PlanController;
use App\Http\Controllers\Web\PrestamoController;
use App\Http\Controllers\Web\RecursoController;
use App\Http\Controllers\Web\RegistroController;
use App\Http\Controllers\Web\ReporteController;
use App\Http\Controllers\Web\SesionController;
use App\Http\Controllers\Web\SolicitudController;
use App\Http\Controllers\Web\SuscripcionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/contratar');

Route::middleware('guest')->group(function (): void {
    Route::get('/contratar', [ContratacionController::class, 'create'])->name('contratacion.create');
    Route::post('/contratar', [ContratacionController::class, 'store'])->name('contratacion.store');
    Route::get('/iniciar-sesion', [SesionController::class, 'create'])->name('login');
    Route::post('/iniciar-sesion', [SesionController::class, 'store'])
        ->middleware('throttle:inicio-sesion')
        ->name('sesion.store');
});

Route::middleware('signed')->group(function (): void {
    Route::get('/completar-registro/{usuario}', [RegistroController::class, 'edit'])->name('registro.completar');
    Route::post('/completar-registro/{usuario}', [RegistroController::class, 'update'])->name('registro.guardar');
});

Route::middleware(['auth', 'suscripcion'])->group(function (): void {
    Route::post('/cerrar-sesion', [SesionController::class, 'destroy'])->name('sesion.destroy');
    Route::get('/panel', PanelController::class)->name('panel');
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::patch('/notificaciones/leidas', [NotificacionController::class, 'marcarTodas'])
        ->name('notificaciones.leidas');
    Route::patch('/notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])
        ->name('notificaciones.leida');

    Route::middleware('rol:superadmin')->group(function (): void {
        Route::get('/instituciones', [InstitucionController::class, 'index'])->name('instituciones.index');
        Route::get('/instituciones/{institucion}', [InstitucionController::class, 'show'])->name('instituciones.show');
        Route::put('/instituciones/{institucion}/plan', [InstitucionController::class, 'cambiarPlan'])
            ->name('instituciones.plan');
        Route::post('/instituciones/{institucion}/renovacion', [InstitucionController::class, 'renovar'])
            ->name('instituciones.renovar');
        Route::put('/instituciones/{institucion}/estado', [InstitucionController::class, 'cambiarEstado'])
            ->name('instituciones.estado');
        Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');
        Route::get('/planes/{plan}/editar', [PlanController::class, 'edit'])->name('planes.edit');
        Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('planes.update');
    });

    Route::middleware('rol:admin_institucional')->group(function (): void {
        Route::get('/suscripcion', [SuscripcionController::class, 'show'])->name('suscripcion.show');
        Route::put('/suscripcion/plan', [SuscripcionController::class, 'cambiarPlan'])->name('suscripcion.plan');
        Route::get('/administradores', [AdministradorController::class, 'index'])->name('administradores.index');
        Route::get('/administradores/crear', [AdministradorController::class, 'create'])
            ->name('administradores.create');
        Route::post('/administradores', [AdministradorController::class, 'store'])->name('administradores.store');
        Route::post('/administradores/{usuario}/reenvio', [AdministradorController::class, 'reenviar'])
            ->name('administradores.reenviar');
    });

    Route::middleware('rol:admin_sistema,admin_institucional')->group(function (): void {
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/exportar', [AuditoriaController::class, 'exportar'])->name('auditoria.exportar');
    });

    Route::middleware('rol:admin_sistema')->group(function (): void {
        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('/areas/crear', [AreaController::class, 'create'])->name('areas.create');
        Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
        Route::get('/areas/{area}/editar', [AreaController::class, 'edit'])->name('areas.edit');
        Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');
        Route::patch('/areas/{area}/activo', [AreaController::class, 'cambiarActivo'])->name('areas.activo');

        Route::get('/recursos/buscar-codigo', [RecursoController::class, 'buscarCodigo'])->name('recursos.buscar');
        Route::get('/recursos/importar', [ImportacionController::class, 'create'])->name('recursos.importar');
        Route::post('/recursos/importar', [ImportacionController::class, 'store'])->name('recursos.importar.store');
        Route::get('/recursos', [RecursoController::class, 'index'])->name('recursos.index');
        Route::get('/recursos/crear', [RecursoController::class, 'create'])->name('recursos.create');
        Route::post('/recursos', [RecursoController::class, 'store'])->name('recursos.store');
        Route::get('/recursos/{recurso}', [RecursoController::class, 'show'])->name('recursos.show');
        Route::get('/recursos/{recurso}/editar', [RecursoController::class, 'edit'])->name('recursos.edit');
        Route::put('/recursos/{recurso}', [RecursoController::class, 'update'])->name('recursos.update');
        Route::patch('/recursos/{recurso}/estado', [RecursoController::class, 'cambiarEstado'])
            ->name('recursos.estado');
        Route::patch('/recursos/{recurso}/activo', [RecursoController::class, 'cambiarActivo'])
            ->name('recursos.activo');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    });

    Route::middleware('rol:admin_sistema,asistente')->group(function (): void {
        Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
        Route::get('/prestamos/exportar', [PrestamoController::class, 'exportar'])->name('prestamos.exportar');
        Route::get('/mostrador', [MostradorController::class, 'index'])->name('mostrador.index');
        Route::post('/mostrador/validacion', [MostradorController::class, 'validar'])->name('mostrador.validar');
        Route::post('/prestamos/{prestamo}/confirmacion', [PrestamoController::class, 'confirmar'])
            ->name('prestamos.confirmar');
    });

    Route::middleware('rol:alumno,docente')->group(function (): void {
        Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
        Route::get('/catalogo/{recurso}', [CatalogoController::class, 'show'])
            ->whereNumber('recurso')
            ->name('catalogo.show');
        Route::get('/mis-prestamos', [SolicitudController::class, 'mis'])->name('prestamos.mis');
        Route::get('/prestamos/nueva', [SolicitudController::class, 'create'])->name('prestamos.create');
        Route::post('/prestamos', [SolicitudController::class, 'store'])->name('prestamos.store');
        Route::post('/prestamos/{prestamo}/token', [SolicitudController::class, 'token'])->name('prestamos.token');
    });

    // Detalle, comprobante y cancelación: la PrestamoPolicy decide por rol, área y propietario.
    Route::get('/prestamos/{prestamo}', [PrestamoController::class, 'show'])
        ->whereNumber('prestamo')
        ->name('prestamos.show');
    Route::post('/prestamos/{prestamo}/cancelacion', [PrestamoController::class, 'cancelar'])
        ->name('prestamos.cancelar');
});
