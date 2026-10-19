<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Pruebas de humo
 * @file         EntornoTest.php
 * @path         tests/Feature/EntornoTest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Prueba de humo del entorno: verifica que MySQL use InnoDB y
 *               utf8mb4, que Redis responda y que el TTL del QR esté dentro
 *               del intervalo permitido.
 *
 * @requisitos   RS-003, RN-004, RNF-011
 *
 * @see          Manual de Desarrollo v1.2, Tabla 23 (prueba de humo)
 * ============================================================================
 */

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

it('se conecta a MySQL con InnoDB y utf8mb4', function () {
    $motor = DB::selectOne('SELECT @@default_storage_engine AS motor');
    $juegoCaracteres = DB::selectOne('SELECT @@character_set_database AS juego');

    expect(DB::connection()->getDriverName())->toBe('mysql')
        ->and($motor->motor)->toBe('InnoDB')
        ->and($juegoCaracteres->juego)->toBe('utf8mb4');
});

it('guarda y expira claves en Redis como lo requieren los tokens QR', function () {
    $clave = 'spei:prueba_humo';

    Redis::connection()->setex($clave, 10, 'ok');

    expect(Redis::connection()->get($clave))->toBe('ok')
        ->and(Redis::connection()->ttl($clave))->toBeGreaterThan(0);

    Redis::connection()->del($clave);
});

it('mantiene el TTL del QR dentro de 30 a 60 segundos', function () {
    $ttl = config('spei.qr.ttl');

    expect($ttl)->toBeGreaterThanOrEqual(config('spei.qr.ttl_minimo'))
        ->and($ttl)->toBeLessThanOrEqual(config('spei.qr.ttl_maximo'));
});
