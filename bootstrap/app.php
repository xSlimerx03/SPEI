<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Arranque de la aplicación
 * @file         app.php
 * @path         bootstrap/app.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-27
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Registro de rutas, middleware del SPEI (tenant, rol y
 *               suscripción) y traducción de las excepciones de enlace firmado.
 *
 * @requisitos   RS-006, RNF-001, RN-011, RN-013, RS-003
 *
 * @see          Manual de Desarrollo v1.2, sección 5.3; DT-008, DT-032
 * ============================================================================
 */

declare(strict_types=1);

use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\SuscripcionMiddleware;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Los nombres propios de contraseña necesitan las mismas protecciones que los de Laravel.
        $middleware->trimStrings(except: ['contrasena', 'contrasena_confirmation']);
        $middleware->web(append: [TenantMiddleware::class]);
        $middleware->api(append: [TenantMiddleware::class]);
        // El tenant se fija después de autenticar y antes de resolver los modelos de la ruta.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: TenantMiddleware::class);
        $middleware->alias([
            'rol' => RolMiddleware::class,
            'suscripcion' => SuscripcionMiddleware::class,
        ]);
        // DT-032: solo se confía en las cabeceras X-Forwarded-* de proxies de la red interna de Docker
        // (nginx, túnel), para generar enlaces HTTPS y registrar la IP real del cliente.
        $middleware->trustProxies(at: ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1']);
        $middleware->redirectGuestsTo(fn (): string => route('login'));
        $middleware->redirectUsersTo(fn (): string => route('panel'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['contrasena', 'contrasena_confirmation', 'codigo']);
        $exceptions->map(
            InvalidSignatureException::class,
            fn (): AccessDeniedHttpException => new AccessDeniedHttpException('El enlace no es válido o ha caducado.'),
        );
    })->create();
