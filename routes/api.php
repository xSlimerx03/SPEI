<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       API REST v1
 * @file         api.php
 * @path         routes/api.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  API REST versionada (/api/v1) con tokens Sanctum, documentada
 *               para un cliente móvil futuro (DT-022).
 *
 * @requisitos   RNF-001, RF-011, RF-015, RS-006
 *
 * @see          docs/api/BBB_API_v1.0.md; DT-007, DT-022
 * ============================================================================
 */

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\RecursoController;
use App\Http\Controllers\Api\V1\SesionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::post('/sesiones', [SesionController::class, 'store'])
        ->middleware('throttle:inicio-sesion')
        ->name('sesiones.store');

    Route::middleware(['auth:sanctum', 'suscripcion'])->group(function (): void {
        Route::delete('/sesiones/actual', [SesionController::class, 'destroy'])->name('sesiones.destroy');
        Route::get('/catalogo', [CatalogoController::class, 'index'])
            ->middleware('rol:alumno,docente')
            ->name('catalogo.index');
        Route::get('/recursos', [RecursoController::class, 'index'])
            ->middleware('rol:admin_sistema')
            ->name('recursos.index');
        Route::get('/recursos/codigo/{codigo}', [RecursoController::class, 'mostrarPorCodigo'])
            ->middleware('rol:admin_sistema')
            ->name('recursos.codigo');
    });
});
