# PLAN DE ITERACIÓN — PI-01

| Campo | Valor |
|---|---|
| Iteración (1) | I0 — Base del proyecto |
| Periodo (2) | 18/10/2026 – 20/10/2026 |
| Fase RUP (3) | Elaboración (cierre) |
| Responsable (4) | Raúl Treviño Licona |

**Objetivo del incremento (5):** entorno ejecutable y reproducible con Laravel 12 (DT-019), MySQL 8.4, Redis 7,
Nginx, CI y estándares de los cuatro manuales listos para iniciar la construcción del bloque A.

## Requisitos y casos de uso (6)

| ID | Descripción | Prioridad | Criterio de aceptación |
|---|---|---|---|
| RS-001 | Backend en Laravel | Alta | `php artisan --version` = Laravel 12.x en PHP 8.3 (DT-019) |
| RS-003 | Gestor MySQL | Alta | Prueba de humo confirma MySQL con InnoDB y utf8mb4 |
| RN-004 | TTL del QR 30–60 s | Alta | `config('spei.qr.ttl')` dentro del rango, verificado por prueba |
| MD 10.3 | Integración continua | Alta | Pipeline con Pint, PHPStan 6, Pest y cobertura ≥ 80 % |

## Tareas y asignación (7)

| Tarea | Responsable | Estimación (h) | Dependencia |
|---|---|---|---|
| Docker (Dockerfile, compose, nginx, php.ini) con digests | spei-devops | 2 | — |
| Esqueleto Laravel 12 + Sanctum 4 + Pest 3 + Larastan 3 | spei-backend | 1.5 | Docker |
| Configuración de calidad (pint, phpstan, editorconfig, gitattributes, analysis_options) | spei-devops | 1 | Esqueleto |
| CLAUDE.md y 9 agentes | spei-orquestador | 2 | — |
| Registro DT-001…DT-020 | spei-documentador | 2 | Análisis de manuales |
| CI, plantilla de PR y pre-commit | spei-devops | 1 | Configuración |
| Plan de desarrollo y RT-01 | spei-orquestador | 1 | DT |

## Riesgos y respuesta (8)

| Riesgo | Probabilidad | Impacto | Tratamiento |
|---|---|---|---|
| Puertos 80/3306 ocupados en el equipo | Alta | Baja | Puertos configurables (DT-006) |
| Dependencias con vulnerabilidades | Media | Media | `composer audit` en CI (DT-019) |

| Aprobó (9) | Fecha (10) |
|---|---|
| Raúl Treviño Licona | 18/10/2026 |
