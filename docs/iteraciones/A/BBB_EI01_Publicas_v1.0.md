# EVALUACIÓN DE INCREMENTO — Pantallas públicas

| Campo | Valor |
|---|---|
| Documento | BBB_EI01_Publicas_v1.0 |
| Iteración | Bloque A, continuación de las pantallas públicas |
| Fecha | 18/10/2026 |
| Versión | 1.0 |
| Responsable del proyecto | Raúl Treviño Licona |
| Estado | Implementado y probado localmente; bloque A todavía en desarrollo |

## Estado encontrado

El repositorio ya contenía las tres pantallas públicas, sus rutas y controladores, además de
suscripciones, planes, administradores, áreas, recursos, catálogo y notificaciones. Los textos
adjuntos de la sesión anterior describían en buena parte código que ya estaba en disco.
Solo existían tres pruebas de infraestructura. Git estaba en `main`, sin commits y con el
código sin seguimiento; este incremento no crea commits ni publica cambios.

Los bloques B, C y D siguen pendientes: carga de usuarios, solicitudes y reservas, entrega y
devolución con QR, sanciones, historial y reportes. La presencia de modelos o enumeraciones
de soporte no equivale a tener esos módulos implementados.

## Cambios realizados

| Hallazgo | Corrección y evidencia |
|---|---|
| Contraseñas con nombres en español podían guardarse en `_old_input` y perder espacios | Exclusiones en `bootstrap/app.php`, `ReglaNegocioException` y recuperación de errores de contratación; regresiones HTTP con contraseña exacta y errores de validación |
| Activación basada en una instancia de usuario desactualizada | Relectura con `lockForUpdate` dentro de la transacción; prueba con dos instancias cargadas antes de activar |
| Faltaban las vistas de los correos | Plantillas `correos/invitacion-administrador` y `correos/alerta-vencimiento`; renderizado y prueba de invitación con `Mail::fake()` |
| Inicio de sesión sin errores asociados a cada campo | Reutilización del campo accesible con `aria-invalid` y `aria-describedby` |
| Contratación disponible sin planes; foco de selección no visible | Botón deshabilitado, validación del plan, error asociado y contorno de foco en las tarjetas |
| Mensajes genéricos de validación sin traducción local | `lang/es/validation.php`, con comprobación de mensajes de contraseña en español |
| El asistente terminaba en 403 al iniciar sesión | Redirección a notificaciones y menú acorde; conserva restricciones de acceso al catálogo |
| PHPStan infería como texto los enums y fechas convertidos por Eloquent | `parseModelCastsMethod: true`; se mantienen nivel 6 y todas las comprobaciones |
| Diferencias de formato del código previo | Aplicación de Pint preservando PHPDoc explícito mediante DT-027 |

## Evidencia ejecutada

Entorno: Docker del proyecto, PHP 8.3.35, MySQL 8.4 y Redis. Base de pruebas:
`spei_testing`, configurada en `phpunit.xml`; las pruebas no usan la base de desarrollo.
Los correos de prueba se interceptan o se renderizan en memoria.

| Comprobación | Resultado |
|---|---|
| Pest: `php artisan test --compact --coverage --min=0` | **28 pruebas correctas, 189 aserciones**: 3 de infraestructura y 25 del incremento |
| PHPStan: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **Sin errores**, nivel 6 |
| Pint: `vendor/bin/pint --test` | **111 archivos correctos** |
| `php artisan view:cache` | Todas las vistas Blade compiladas correctamente |
| Servidor local: `/iniciar-sesion`, `/contratar`, `/css/spei.css` | **HTTP 200** en las tres rutas |
| Cobertura global de `app/` | **38.6%**, inferior al requisito de 80% |
| Cobertura de `OnboardingService` | 97.6% |
| Cobertura de `AdministradorService` | 91.8% |
| Cobertura de `AutenticacionService` | 86.4% |

`--min=0` se utilizó exclusivamente para medir la línea base de cobertura. La configuración
de CI y el umbral obligatorio de **80% no fueron reducidos**. No se declara CI en verde ni
cumplimiento completo del bloque A. El comando equivalente con `--min=80` no alcanzaría el umbral.

Los casos de regresión están en `tests/Feature/PantallasPublicasTest.php`:

- Pantallas públicas, planes activos y estado sin planes.
- Alta institucional con vigencia, límites, contraseña y rol controlados por el servidor.
- Rechazo de correo ajeno, plan inexistente y contraseñas inválidas sin crear registros.
- Acceso y cierre de sesión de los seis roles; auditoría de acceso.
- Denegación de cuenta inactiva y suscripción suspendida; sexto intento devuelve 429.
- Invitaciones válidas, alteradas, vencidas, ya activadas y enviadas con contraseña inválida.
- Segunda activación con instancia obsoleta; no sustituye una prueba de concurrencia real.
- Generación del correo desde el formulario de invitación, enlace funcional y escape HTML.

## Pendientes y siguiente incremento

1. Ampliar pruebas funcionales y de aislamiento entre instituciones para el resto del bloque A:
   planes, vigencia, límites, áreas, recursos, importación CSV y API. Alcanzar 80% de cobertura.
2. Revisar visualmente escritorio y móvil y probar teclado en navegador. No hubo navegador
   conectado disponible en esta sesión; las pruebas HTTP y el HTML no sustituyen esa revisión.
3. Completar documentación de datos, diagramas y evidencias exigidas por la iteración. La matriz
   RT-01 se actualiza con evidencia parcial, sin marcar los requisitos como aprobados.
4. Mantener pendiente la revisión independiente y aceptación del incremento. DT-023 a DT-027
   conservan sus estados propuestos; este informe no cambia las aprobaciones anteriores.
5. Después de consolidar el bloque A, desarrollar el bloque B: usuarios, roles y reservas.

Las invitaciones actuales dependen de cuenta inactiva y firma temporal. Si se introduce una
baja y reactivación de administradores en otro incremento, será necesario distinguir una cuenta
pendiente de registro de una cuenta desactivada y revocar invitaciones anteriores.

## Reproducción local

Consulte `README.md` para iniciar Docker, cargar planes y ejecutar el trabajador de colas.
Las pantallas se encuentran en `/iniciar-sesion` y `/contratar`; completar registro requiere
el enlace firmado de una invitación. No existe una ruta pública de registro libre de usuarios.
