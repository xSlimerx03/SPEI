# EVALUACIÓN DE ITERACIÓN — EI-01

| Campo | Valor |
|---|---|
| Iteración (1) | I0 — Base del proyecto |
| Fecha (2) | 18/10/2026 |

**Objetivo y resultado presentado (3):** el entorno se levanta con `docker compose up -d` (app, web,
db, redis, phpmyadmin, mailhog); Laravel 12.70.0 sobre PHP 8.3 responde en `http://localhost:8000`.

## Cumplimiento (4)

| Requisito | Resultado | Evidencia | Estado |
|---|---|---|---|
| RS-001 | Laravel 12.70.0 (DT-019), Sanctum 4.3.3, PHP 8.3 | `composer show -D` | Cumple |
| RS-003 | MySQL 8.4, InnoDB, utf8mb4 | `tests/Feature/EntornoTest.php` | Cumple |
| RN-004 | TTL 45 s dentro de 30–60 s | `tests/Feature/EntornoTest.php` | Cumple |
| MD 10.3 | `.github/workflows/ci.yml` con los 6 pasos | Archivo en el repositorio | Cumple (sin ejecutar en GitHub: no hay remoto) |

## Calidad y defectos (5)

| Prueba o control | Resultado | Defecto | Severidad |
|---|---|---|---|
| Pest | 3 pruebas / 9 aserciones aprobadas | — | — |
| Laravel Pint `--test` | 29 archivos sin incidencias | — | — |
| PHPStan nivel 6 (Larastan 3.13, PHPStan 2.3.1) | Sin errores | — | — |
| Cobertura `--min=80` | 33,3 %: solo `app/Models/User.php` (0 %), plantilla del framework que se reemplaza por `Usuario` en I1 | DEF-001 | Media |
| `composer audit` | 4 avisos de `laravel/framework` 11.x sin parche → **Cerrado** con Laravel 12.70.0: "No security vulnerability advisories found" | DEF-002 | Alta |

## Lecciones y acciones (6)
- DEF-001 se cierra al implementar `usuarios` (MBD 4.4) en el bloque A.
- DEF-002 **cerrado** el 18/10/2026 por DT-019 (actualización a Laravel 12.70.0, regresión en verde).
- Antes del bloque A: fijar la línea base de requisitos (SRS de 18 o 24 RF vs. catálogo de 58 RF de los manuales).

| Decisión (7) | Aprobó (8) |
|---|---|
| Aceptado [ ] · Corrección [x] (DEF-001) · Reprogramado [ ] | Pendiente — Raúl Treviño Licona |
