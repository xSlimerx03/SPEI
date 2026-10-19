# SPEI — Sistema de Préstamo de Equipo Institucional

Plataforma SaaS multi-tenant de préstamo de equipo (Bigbitbyte, Auditoría de Sistemas).
Backend Laravel 12 / PHP 8.3 (API REST + web Blade), MySQL 8.4, Redis 7, Docker. Sin app móvil (DT-022).

## Arranque
```bash
cp .env.example .env            # completar los valores <definir>
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
git config core.hooksPath .githooks
```
Aplicación: http://localhost:8000 · phpMyAdmin: http://localhost:8080 · MailHog: http://localhost:8025

## Pantallas públicas y verificación

- Inicio de sesión: http://localhost:8000/iniciar-sesion
- Contratación: http://localhost:8000/contratar (requiere planes activos).
- Completar registro: enlace firmado enviado al invitar un administrador desde la aplicación; caduca a las 72 horas.

Para cargar los planes iniciales y procesar los correos del entorno local:

```bash
docker compose exec app php artisan db:seed --class=PlanesSeeder
docker compose exec app php artisan queue:work
```

El proceso de colas permanece en ejecución. Revise las invitaciones locales en MailHog; los correos
de las pruebas se simulan y no se envían a destinatarios reales.

Ejecute las pruebas dentro de Docker: los nombres `db` y `redis` pertenecen a esa red. Las pruebas
usan exclusivamente `spei_testing`, cuyo contenido es descartable; no cambie esa configuración
para apuntar a una base con datos que quiera conservar.

```bash
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app vendor/bin/phpstan analyse --no-progress --memory-limit=1G
docker compose exec app vendor/bin/pest --coverage --min=80
```

El umbral de cobertura corresponde a todo el backend y requiere ampliar las pruebas de los demás
módulos. Estado y pendientes del incremento: `docs/iteraciones/A/BBB_EI01_Publicas_v1.0.md`.

## Documentación
- Reglas del proyecto y precedencia de manuales: `CLAUDE.md`
- Decisiones técnicas: `docs/decisiones/BBB_DT_Registro_v1.0.md`
- Plan de desarrollo: `docs/plan/BBB_PlanDesarrollo_v1.0.md`
- Trazabilidad: `docs/trazabilidad/BBB_RT01_Matriz_v1.0.md`
