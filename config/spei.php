<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Configuración general
 * @file         spei.php
 * @path         config/spei.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Parámetros de negocio configurables del SPEI: vigencia del
 *               token QR, tolerancia de recogida, alertas de suscripción,
 *               invitaciones, importación CSV, catálogo y cuenta superadmin.
 *
 * @requisitos   RF-005, RF-008, RF-012, RF-015, RF-032, RF-034, RF-036, RN-004, RN-007
 *
 * @see          Manual de Desarrollo v1.2, sección 5.5 y Tabla 25; CU-002, CU-004, CU-008, CU-010
 * ============================================================================
 */

declare(strict_types=1);

return [
    'qr' => [
        // Vigencia del token en segundos; el MD 5.5 solo permite 30 a 60 s.
        'ttl' => (int) env('SPEI_QR_TTL', 45),
        'ttl_minimo' => 30,
        'ttl_maximo' => 60,
    ],

    'prestamos' => [
        // Minutos de espera antes de cancelar por no recoger (MD Tabla 25).
        'tolerancia_recogida' => (int) env('SPEI_TOLERANCIA_RECOGIDA', 15),
        'tolerancia_maxima' => 20,
    ],

    'suscripcion' => [
        // Días antes del corte en que se avisa al administrador institucional (RF-005).
        'dias_alerta' => [7, 3, 1],
    ],

    'invitacion' => [
        // Vigencia del enlace firmado para completar el registro (DT-025).
        'vigencia_horas' => 72,
    ],

    'importacion' => [
        // Límites de la carga masiva por CSV (CU-008).
        'maximo_filas' => 1000,
        'maximo_kb' => 2048,
    ],

    'catalogo' => [
        'por_pagina' => 12,
    ],

    'superadmin' => [
        // Credenciales iniciales del superadmin; solo las lee SuperadminSeeder.
        'nombre' => env('SPEI_SUPERADMIN_NOMBRE', 'Superadministrador SPEI'),
        'correo' => env('SPEI_SUPERADMIN_CORREO'),
        'contrasena' => env('SPEI_SUPERADMIN_CONTRASENA'),
    ],

    'demo' => [
        // Contraseña de los usuarios de DemoSeeder (solo APP_ENV=local).
        'contrasena' => env('SPEI_DEMO_CONTRASENA'),
    ],
];
