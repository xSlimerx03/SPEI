# SPEI — Sistema de Préstamo de Equipo Institucional (Bigbitbyte)

Este repositorio implementa la liberación 1.0 del SPEI y debe cumplir **al 100 %** los cuatro manuales
del proyecto (versión 1.2). Este archivo resume las reglas obligatorias; ante cualquier duda se consulta
el manual original en `C:\Users\Lenovo\Downloads\Manuales_Correccion_PrimeraAudi\Manuales Corrección 1ra Auditoria\`.

## 0. Modo de trabajo

- **Desarrollador único:** Raúl Treviño Licona. Se omiten todos los procesos que dependen de otras
  personas (aprobaciones de David o del docente, revisión por otro integrante, actas AR-01 / JD-01,
  SharePoint compartido). Raúl aprueba.
- La **revisión independiente** que exigen MD 3.3, MD 10.2 y MC 1.8 la realiza el subagente
  `spei-auditor`, que nunca es el autor del cambio.
- Los agentes **no aprueban ni declaran terminado** nada: proponen y presentan evidencia.

## 1. Precedencia normativa (MD 1.5)

1. Manual de Calidad **BBB-CA-001** (formato, control documental, auditoría).
2. Manual de Desarrollo **BBB-MD-002** (RUP, arquitectura, iteraciones, evidencias).
3. Manual de Programación **BBB-MP-003** y Manual de Base de Datos **BBB-BD-004**.

Si dos manuales se contradicen, gana el de mayor precedencia. Los conflictos ya resueltos están en
`docs/decisiones/BBB_DT_Registro_v1.0.md` (DT-001 a DT-022). Todo conflicto nuevo se documenta con un
DT-01 antes de implementarse.

## 2. Stack obligatorio (MD 6, Tablas 14–17)

| Capa | Tecnología |
|---|---|
| Backend / API | PHP 8.3 (mínimo 8.2), **Laravel 12.x** (DT-019: 11.x sin parches de seguridad), Sanctum 4.x, Blade |
| Datos | MySQL 8.4 LTS / InnoDB, utf8mb4 / utf8mb4_unicode_ci; Redis 7.x (caché, colas, locks, tokens QR) |
| Calidad | Pest 3.x (cobertura ≥ 80 % backend crítico), PHPStan 2.x nivel 6 (Larastan), Laravel Pint (PSR-12) |
| Infraestructura | Docker Compose; imágenes fijadas por etiqueta **y digest** (MD 6.1) |
| Iconos | Material Icons (obligatorio); un solo set por pantalla |

Arquitectura: **monolito modular por capas** (MD 5.1). Módulos y RF en MD Tabla 13.

**Alcance (DT-022):** solo el backend Laravel (API REST + vistas web Blade responsivas). La app móvil
Flutter de MD 6 queda **fuera de alcance**: no se crea código Dart ni agente móvil. La API REST se
mantiene documentada para un cliente móvil futuro.

## 3. Reglas de código (MP cap. 1)

- **Cabecera de prólogo** obligatoria en todo archivo propio (`.php`, `.blade.php`, `.sql`, `.yaml`, Docker):
  bloques @project, @module, @file, @path, @author, @date, @lastModified, @version, @license,
  @description, @requisitos, @see (MP 1.1). Fechas ISO 8601. Se actualiza @lastModified en cada cambio.
  No llevan cabecera los archivos generados por el framework sin modificar.
- PHP: `declare(strict_types=1);`, 4 espacios, 120 caracteres, llaves de clase y método en línea
  siguiente, de control en la misma línea, `use` ordenados alfabéticamente, coma final en multilínea,
  comillas simples, `match` para asignaciones simples, promoción de propiedades en constructores.
- SQL: palabras clave en MAYÚSCULAS, identificadores snake_case, 4 espacios, 100 caracteres.
- UTF-8 sin BOM, fin de línea LF, sin tabulaciones.
- Tipos de parámetros y retorno **siempre explícitos**; PHPDoc en clases y métodos públicos
  y protegidos (@param, @return, @throws).
- Máximo **3 niveles de anidamiento**, **5 parámetros** y **50 líneas** por método; *early return*;
  sin ternarios anidados; sin `goto`; catch de excepciones específicas, nunca `Exception` genérica.
- Nomenclatura (MP 1.5): variables y métodos camelCase con verbo en infinitivo; clases PascalCase con
  sufijo (Controller, Service, Repository, Middleware, Seeder); vistas snake_case / kebab-case; rutas kebab-case. Identificadores **en español, sin acentos ni ñ**.
- Orden interno de una clase (MP 1.7): constantes → propiedades → constructor → métodos públicos →
  protegidos → privados. Un archivo = una clase.
- Comentarios en español, explican el *por qué*; etiquetas TODO:, FIXME:, NOTE:, WARNING:. Sin código
  comentado.

## 4. Reglas de base de datos (MBD)

- Tablas plural snake_case; columnas singular; PK `id_<entidad>` BIGINT UNSIGNED AUTO_INCREMENT;
  FK `id_<entidad_referenciada>` con restricción `fk_<origen>_<destino>`; UK `uk_<tabla>_<cols>`;
  índices `idx_<tabla>_<cols>` empezando por `id_institucion`; triggers `trg_<tabla>_<momento>_<evento>`;
  SP `sp_<accion>_<entidad>` (parámetros `p_`, variables `v_`); vistas `vw_<descripcion>`.
- Toda tabla y columna lleva `COMMENT` en español. Toda tabla lleva `fecha_creacion` y, si aplica,
  `fecha_actualizacion`. Los modelos Eloquent declaran `$table`, `$primaryKey`, `CREATED_AT` y `UPDATED_AT`.
- **Multi-tenant:** esquema compartido con `id_institucion` en toda entidad del tenant (DT-008).
  Global Scope + Policies + `TenantMiddleware`. Las pruebas intentan accesos cruzados y verifican el rechazo.
- Las cascadas siguen MBD Tabla 27 (DT-009). `logs_auditoria` es de solo inserción (DT-010).
- Los cambios de estructura se hacen **solo con migraciones** (MD 9.4) y cada uno se acompaña de su
  formato DDL-01…DDL-08 en `docs/bd/` con script de rollback y validaciones previas y posteriores.
- Reservas: transacción + `lockForUpdate` + lock Redis; prioridad RN-008: docente > alumno → mejor
  historial (menos marcas) → `fecha_creacion` más antigua (MD 5.6).

## 5. UI / UX (MP cap. 2)

Paleta: azul cielo `#3B82F6` (primario), azul claro `#60A5FA`, azul oscuro `#1F2937`, blanco suave
`#F0F9FF`; estados éxito `#10B981`, advertencia `#F59E0B`, error `#EF4444`, información `#3B82F6`;
texto secundario `#6B7280`, deshabilitado `#9CA3AF`. Contraste WCAG AA. Tamaños de componentes,
radios, tipografía, layouts, menús, mensajes y textos de acción: MP Tablas 52–78. Botones con verbo en
infinitivo, máximo 2 palabras, sin punto final. Licencias de iconos y recursos en `CREDITS.md`.

## 6. Flujo de trabajo (MD 10, MP 3.2)

- Ramas: `main` (liberado), `develop` (integración), `feature/RF-XXX-descripcion`,
  `fix/DEF-XXX-descripcion`, `hotfix/vX.Y.Z-descripcion`, `docs/BBB-codigo-descripcion` (DT-001).
- Conventional Commits en español: `feat:`, `fix:`, `docs:`, `style:`, `refactor:`, `test:`, `chore:`.
- Nunca se integra directo a `main` o `develop`; todo entra por rama + revisión del `spei-auditor` +
  CI verde. Etiquetas `vMAJOR.MINOR.PATCH`.
- CI (MD 10.3): dependencias con composer.lock → Pint `--test` → PHPStan 6 → Pest → cobertura
  ≥ 80 % → `composer audit` → build.
- Secretos solo en `.env` (excluido de Git); `.env.example` documenta cada variable sin credenciales.

## 7. Definición de terminado (MD 10.4)

Un RF está terminado solo si: tiene CU-01 / criterios de aceptación; diseño y diagramas actualizados;
compila y pasa Pint, PHPStan y la revisión del auditor; migraciones reproducibles y reversibles; casos
CP-01 ejecutados con cobertura registrada y sin defectos bloqueantes; autorización, tenant, validación
y logs revisados; API, configuración, RT-01 y evidencia actualizadas; y Raúl lo acepta en el EI-01.

## 8. Comandos

```bash
docker compose up -d                                   # levantar entorno
docker compose exec app php artisan migrate            # migraciones
docker compose exec app ./vendor/bin/pint --test       # estilo PSR-12
docker compose exec app ./vendor/bin/phpstan analyse   # análisis estático nivel 6
docker compose exec app ./vendor/bin/pest --coverage --min=80
```

## 9. Agentes (`.claude/agents/`)

| Agente | Disciplina RUP | Uso |
|---|---|---|
| `spei-orquestador` | Gestión del proyecto | PI-01, EI-01, RT-01, criterios de entrada y salida, riesgos |
| `spei-analista` | Requisitos / análisis | CU-01, REQ-01, DA-01, criterios de aceptación |
| `spei-datos` | Diseño de datos | Migraciones, DDL-01…08, diccionario, ER |
| `spei-backend` | Implementación | Laravel, API, Blade, servicios, colas, QR web |
| `spei-qa` | Pruebas | Pest, CP-01, cobertura, defectos |
| `spei-devops` | Despliegue / ambiente | Docker, CI, LI-01 |
| `spei-documentador` | Configuración | Formatos con formato MC, DG-01, diagramas |
| `spei-auditor` | SQA (solo lectura) | LV-01, RED-01, NC-01 contra los 4 manuales |

Plan vigente: `docs/plan/BBB_PlanDesarrollo_v1.0.md`.
