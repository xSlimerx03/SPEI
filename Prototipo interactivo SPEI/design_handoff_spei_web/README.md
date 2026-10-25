# Handoff: SPEI — Interfaz web (Laravel 11 + Blade)

## Overview
Rediseño UX/UI de la aplicación web del **Sistema de Préstamo de Equipo Institucional (SPEI)** de Bigbitbyte. Cubre los RF-001 a RF-058 que tienen interfaz web, con cuatro roles: Administrador del sistema, Asistente de mostrador, Administrador institucional y Usuario final (alumno/docente). La app móvil Flutter queda fuera de este paquete.

## About the Design Files
`SPEI Prototipo.dc.html` es una **referencia de diseño hecha en HTML**: un prototipo que muestra el aspecto y el comportamiento esperados. **No es código de producción para copiar.** La tarea es **recrear estas pantallas en el proyecto Laravel 11 existente** con vistas Blade, layouts (`@extends`/componentes Blade), Form Requests, policies y rutas, según el Manual de Programación BBB v1.2. Para la interactividad ligera (tabs, modales, toasts, contador del QR) usa Alpine.js o Livewire, a decisión del equipo (si aplica, regístralo en DT-01).

Para ver el prototipo, abre el HTML en un navegador; `support.js` debe estar en la misma carpeta. Usa el menú del avatar (arriba a la derecha) → "Ver como" para cambiar de rol.

## Fidelity
**Alta fidelidad (hi-fi).** Colores, tipografía, espaciados, estados y textos son finales y deben reproducirse con exactitud. Los datos son ficticios y deben venir de la BD (`instituciones`, `usuarios`, `recursos`, `prestamos`, `detalle_prestamo`, `tokens_qr`, `penalizaciones`, `matriz_sanciones`, `notificaciones`, `conversaciones`, `logs_auditoria`).

## Design Tokens
**Colores**
| Token | Hex | Uso |
|---|---|---|
| primary | #3B82F6 | Acentos, ítem activo del sidebar, gráficas, foco de inputs |
| primary-strong | #2563EB | Fondo de botón primario y enlaces (contraste AA con blanco) |
| primary-hover | #1D4ED8 | Hover de botón primario, texto de folios |
| dark | #1F2937 | Texto principal, sidebar, panel del escáner |
| bg | #F0F9FF | Fondo de la app, encabezados de tabla |
| card | #FFFFFF | Tarjetas, topbar, modales |
| text-secondary | #6B7280 | Texto secundario |
| text-muted | #4B5563 / #374151 | Texto de apoyo |
| disabled / input-border | #9CA3AF | Borde de inputs (1px), deshabilitado |
| border-soft | #CBD5E1 | Borde de botón secundario |
| divider | #EEF2F7 / #F1F5F9 | Separadores de fila |
| success | #10B981 | Íconos de éxito, disponibilidad |
| warning | #F59E0B | Pendientes, recordatorios |
| error | #EF4444 | Errores, sanciones, contador de notificaciones |
| overlay | rgba(31,41,55,0.5) | Fondo de modales |

**Badges de estado** (alto 24px, radio 4px, padding 0 8px, 12px bold)
- Pendiente: bg #FEF3C7 / texto #92400E
- Activo / Prestado / Aprobada: #DBEAFE / #1E40AF
- Devuelto / Disponible: #D1FAE5 / #065F46
- Vencido / Bloqueado (usuario): #FEE2E2 / #991B1B
- Cancelado / Mantenimiento: #F3F4F6 / #4B5563
- Bloqueado (recurso): #FEF3C7 / #92400E

**Tipografía**
- UI: `Calibri, Carlito, 'Segoe UI', system-ui, sans-serif`. Carlito, de Google Fonts, sustituye a Calibri con las mismas medidas. Pesos 400 y 700.
- Folios, inventarios, timestamps y tokens: `'Fira Code', ui-monospace, monospace`, 11–13px.
- Escala (en px; el manual la expresa en pt): título de pantalla 24/700 · subtítulo 18/700 · título de tarjeta 16/700 · cuerpo 14/400 · secundario 13/400 · etiqueta 13/700 · botón 14/700 · encabezado de tabla 12/700, mayúsculas, tracking 0.04em · KPI 36/700.

**Radios:** botones/inputs 8px · tarjetas 12px · modales 16px · badges 4px · avatares 50% · switch 16px.
**Sombras:** tarjeta `0 0 0 1px rgba(31,41,55,.06), 0 2px 4px rgba(31,41,55,.06)` · topbar `0 1px 3px rgba(0,0,0,.1)` · botón `0 1px 2px rgba(0,0,0,.05)` · modal `0 4px 6px rgba(0,0,0,.1), 0 24px 48px rgba(31,41,55,.2)` · dropdown `0 0 0 1px rgba(31,41,55,.08), 0 16px 40px rgba(31,41,55,.16)`.
**Espaciado:** padding del contenido 32px · gap entre tarjetas 24px · padding de tarjeta 20px (24 en detalle y formularios) · filas de tabla 12px 16px · contenedor máximo 1280px · formulario máximo 720px · modal máximo 600px.

**Componentes**
- Botón primario: alto 48 (40 en barras de herramientas), padding 0 24/20, bg #2563EB, texto blanco 14/700, ícono 20px a la izquierda, gap 8.
- Botón secundario: alto 40, padding 0 20, bg blanco, borde 1px #CBD5E1; hover bg #F0F9FF y borde #93C5FD.
- Botón pequeño: alto 32, padding 0 14–16, 13px.
- Input/Dropdown: alto 48 (40 en filtros), borde 1px #9CA3AF, radio 8, padding 0 16; foco: borde 2px #3B82F6. Si lleva ícono, va a 14px de la izquierda y el padding izquierdo es de 44px.
- Textarea: mínimo 120px, redimensionable en vertical.
- Switch: 56×32, thumb de 24px.
- Tabs: alto 44, subrayado de 2px #3B82F6 en la activa, con contador en una píldora de 20px.
- Toast: abajo a la derecha (24px), radio 12; éxito bg #047857, info #1F2937, advertencia #92400E; dura 3 s (info 5 s).

## Layout global
- **Sidebar** (`aside`): 240px expandido / 64px colapsado, transición de ancho 0.2s, bg #1F2937.
  - Logo: tile azul de 32px con "B", "SPEI" y "por Bigbitbyte".
  - Debajo, tarjeta del tenant: nombre de la institución, plan y dominio.
  - Secciones con título de 11px en mayúsculas #9CA3AF.
  - Ítems de 44px de alto con ícono de 24px; el activo lleva bg #3B82F6 y texto blanco, los inactivos texto #CBD5E1. El contador va en píldora.
  - Abajo, el botón "Contraer menú".
- **Topbar**: 64px, blanco, sticky.
  - A la izquierda, breadcrumb (12px) y título (24/700), con mínimo de 160px.
  - Buscador `flex: 0 1 300px`.
  - Campana con contador rojo, que abre un dropdown de notificaciones de 380px.
  - Avatar de 40px con nombre y rol, que abre un menú de perfil (cambio de rol solo en el demo, y "Cerrar sesión").
- **Banner de solo lectura** (RF-006), bajo el topbar: bg #FFFBEB y borde #FDE68A, con el botón "Renovar plan".
- **Menú por rol** (máximo 7 ítems):
  - Admin. del sistema: Dashboard, Préstamos, Recursos | Usuarios, Sanciones, Reportes, Auditoría
  - Asistente: Entregas y devoluciones, Préstamos, Recursos
  - Admin. institucional: Suscripción, Auditoría
  - Usuario final: Catálogo, Nueva solicitud, Mis préstamos

## Screens / Views
1. **Login.** Tarjeta centrada de máximo 450px sobre un fondo con retícula de 32px (líneas rgba(59,130,246,.07)).
   - Arriba: logo, título "Iniciar sesión" e institución detectada con la opción "Cambiar".
   - Formulario: correo institucional, contraseña con mostrar/ocultar y "Mantener sesión iniciada".
   - Acciones: botón primario "Iniciar sesión", separador "o" y "Continuar con SSO institucional" (opcional, RNF-001).
   - Pie: enlace "¿Olvidaste tu contraseña?" y la nota "Tu rol se asigna automáticamente según tu dominio institucional."
2. **Dashboard** (RF-049, 054)
   - Saludo con la fecha y botones "Ver reportes" y "Revisar solicitudes".
   - 4 KPIs (auto-fit, mínimo 220px): activos, pendientes, vencidos y disponibles, cada uno con ícono en un tile de 40px.
   - Gráfica "Préstamos por día" de 14 días, con barras #BFDBFE y la de hoy en #3B82F6, que ocupa 2 columnas.
   - "Estado del inventario": barra apilada más una leyenda con conteo y porcentaje.
   - "Solicitudes por atender" (2 columnas), con badge "Conflicto" y botón "Revisar".
   - "Agenda de mostrador": hora en monoespaciada y chip de estado.
3. **Préstamos** (RF-022 a 033)
   - Tabs por estado con contadores.
   - Barra de filtros: búsqueda, área, rango de fechas y "Filtrar".
   - Tabla en grid: Folio, Solicitante (avatar, rol y área), Recursos (+N más), Horario y Estado (badge y subestado). La fila seleccionada lleva bg #EFF6FF.
   - Paginación.
   - **Panel de detalle** (400px, sticky):
     - Cabecera con folio, badge e indicador de marcas (barra verde, ámbar o roja según 0, 1–2 o 3 marcas).
     - Alerta de conflicto (RF-027) y nota de cancelación.
     - Fecha, horario, motivo y recursos.
     - **Conversación** previa a la aprobación (RF-033): burbujas propias en #2563EB, ajenas en #F0F9FF, y un input con botón de enviar.
     - Acciones según el estado:
       - Por aprobar: Rechazar / Aprobar.
       - Aprobada: "Cancelar por prioridad" (RF-030), que abre un modal con justificación obligatoria de al menos 20 caracteres.
       - Activo: aviso de "no se permiten prórrogas" (RF-031) y "Descargar comprobante".
4. **Recursos / Catálogo** (RF-010 a 015)
   - Chips de estado con conteo; el activo va en bg #1F2937.
   - Para admin: "Escanear código" (RF-011), "Importar CSV" (RF-012) y "Crear".
   - Filtros: búsqueda, área, tipo y un conmutador cuadrícula/lista.
   - Cuadrícula `auto-fill, minmax(240px,1fr)`. Cada tarjeta lleva imagen 4:3 con badge de estado y tipo, nombre, área e inventario, y un pie con la disponibilidad; el usuario final ve además el botón "Solicitar".
   - Hover: se eleva 2px con sombra.
   - Si no hay resultados: estado vacío con un ícono de 64px.
5. **Detalle de recurso**
   - Layout en 2 columnas (información a la izquierda, imagen a la derecha).
   - Izquierda:
     - Badges, nombre (28/700), área e inventario.
     - Caja de disponibilidad, descripción y acciones: "Solicitar préstamo" para el usuario; "Editar" y "Marcar en mantenimiento" (RF-014) para el admin.
     - Línea de tiempo semanal de 7:00 a 22:00 con bloques reservados (#DBEAFE/#3B82F6) y por aprobar (#FEF3C7/#F59E0B).
   - Derecha: imagen principal, 4 miniaturas, especificaciones y el historial reciente (admin).
6. **Nueva solicitud** (RF-022 a 026, usuario final). Contenedor de 720px con un stepper de 3 pasos: círculo de 36px, activo #2563EB, hecho #10B981 con check.
   - Paso 1: recursos (multirecurso) y "Agregar otro recurso".
   - Paso 2: calendario mensual (lunes primero; los días pasados y los domingos van deshabilitados), hora de inicio y fin, motivo, y una alerta de disponibilidad en tiempo real. Si está libre, sale en verde: "Disponible…". Si hay choque, sale en ámbar: "El recurso está reservado en ese horario."
   - Paso 3: resumen de recursos, fecha y punto de entrega; reglas (tolerancia de 15 min, QR de un solo uso, sin prórrogas) y aceptación del reglamento.
   - Pie: Cancelar / Anterior / Siguiente, y "Enviar solicitud" en el último paso.
7. **Mis préstamos** (RF-028, 034, 036, 040, 051)
   - Tabs "Activos y próximos" e "Historial".
   - Tarjetas con folio, badge, fechas y una nota contextual. Acciones: "QR de entrega", "QR de devolución", "Mensajes" o "Cancelar solicitud".
   - Historial con "Comprobante PDF".
   - Lateral con el indicador de marcas (0 de 3) y estadísticas.
   - **Modal QR:**
     - Código de 250px.
     - Contador de 45 s con barra que pasa a rojo en los últimos 10 s.
     - Al expirar, overlay con "Generar nuevo".
8. **Mostrador** (RF-034 a 039, asistente)
   - Barra de conexión: chip En línea/Sin conexión, "N eventos pendientes de sincronizar" con botón "Sincronizar", y switch para simular la falta de conexión.
   - Panel del escáner (bg #1F2937): vista de cámara con un marco de 4 esquinas azules, "Simular escaneo" y un input manual.
   - Resultado del escaneo: tarjeta con anillo verde.
     - Token válido y su expiración, y el usuario.
     - Condición por recurso con un control segmentado Bueno/Regular/Malo.
     - "Registrar incidencia", que abre un modal con 4 tipos (marcas según la matriz), descripción y evidencia.
     - Cancelar / "Confirmar entrega".
   - Agenda del día.
9. **Usuarios** (RF-016 a 021)
   - "Carga masiva" con zona de arrastre CSV, plantilla y resultado de la última carga.
   - "Mapeo de roles por dominio": @alumnos… → Alumno, @dominio → Docente, manual → Staff.
   - Tabla con tabs (Todos / Responsables / Asistentes / Bloqueados): usuario, rol, área asignada, marcas y estado.
   - Botones "Asignar asistente" y "Crear responsable".
10. **Sanciones** (RF-041 a 045)
    - Matriz editable con steppers (−/valor/+), un valor de marcas por tipo de incidencia.
    - Umbral de bloqueo (3) y duración (7 días), con "Guardar".
    - Lista de sanciones recientes con "Exentar", que abre un modal con motivo obligatorio de al menos 10 caracteres.
11. **Reportes** (RF-054 a 058)
    - Selector segmentado de periodo, filtro de área, y "Exportar CSV" / "Exportar PDF".
    - 4 tarjetas: equipos más prestados (barras horizontales), tiempo promedio de uso, incidentes y sanciones, y cancelaciones por motivo (barra apilada en 4 tonos de azul).
12. **Auditoría** (RF-052, 053)
    - Aviso "Registro inalterable" con la política de retención.
    - Filtros: búsqueda, acción, tabla y fecha.
    - Tabla monoespaciada: fecha y hora, usuario, acción (chip), tabla, registro, detalle y resultado (Éxito / Rechazado / Parcial).
13. **Suscripción** (RF-001 a 008, admin institucional)
    - Alerta de vencimiento (RF-005).
    - Tarjeta oscura del plan: vigencia con barra de progreso, "Renovar" y "Cancelar suscripción".
    - Uso del plan: áreas, recursos y administradores.
    - 3 planes; el actual con un anillo de 2px #3B82F6.
    - Tabla de administradores del sistema con "Crear" (RF-008).

## Interactions & Behavior
- **Navegación:** al cambiar de ruta, scroll arriba. Dropdowns de notificaciones y perfil exclusivos entre sí.
- **Toasts:** se muestran tras aprobar, rechazar, cancelar, exportar, guardar y sincronizar, y se cierran solos.
- **Aprobar solicitud:** pasa a "Aprobada · por recoger", bloquea el recurso y notifica por push y correo.
- **Rechazar solicitud:** pasa a Cancelado y libera los recursos.
- **Cancelar por prioridad:** el botón Confirmar queda deshabilitado (#FCA5A5) hasta tener ≥ 20 caracteres; al confirmar se registra en `logs_auditoria` y se notifica al usuario (RF-050).
- **QR:** TTL de 45 s (configurable entre 30 y 60); el token de Redis es de un solo uso; al expirar, se regenera bajo demanda.
- **Sin conexión:** las confirmaciones se guardan como "pendiente de sincronización" y aumentan el contador. Al recuperar la conexión, se reintenta a los 5, 15, 30 y 60 s, sin duplicar eventos.
- **Hover:** filas de tabla bg #F8FBFF; botones secundarios bg #F0F9FF.
- **Foco:** borde de 2px #3B82F6; todos los controles deben ser accesibles por teclado.
- **Responsive:** las grids usan `auto-fit/minmax`; en móvil el sidebar se colapsa a 64px y las tablas tienen scroll horizontal.

## State Management
Estado de servidor: listado y detalle de préstamos, conversación, catálogo filtrado, disponibilidad por rango, notificaciones no leídas, estado de la suscripción (activa / solo lectura), matriz de sanciones, eventos pendientes de sincronizar (mostrador).
Estado local de UI: tab activa, fila seleccionada, vista cuadrícula/lista, paso del stepper, día elegido, modal abierto, borradores de mensaje y de justificación, segundos restantes del QR, condición por recurso.
Todas las consultas se filtran por `id_institucion` (multi-tenant) y se autorizan con policies por rol.

## Assets
- Íconos: **Material Symbols Outlined** (Apache 2.0) por Google Fonts; registrar en CREDITS.md.
- Fuentes: Carlito (OFL) como sustituto de Calibri y Fira Code (OFL).
- Las imágenes de recursos y el logotipo de BigBitByte son **placeholders a rayas**; usar los activos reales (SVG del logo según el manual, sec. 2.3).

## Files
- `SPEI Prototipo.dc.html`: prototipo completo con todas las pantallas, modales y datos de ejemplo (el arreglo de datos está en el `<script>` de lógica, al final del archivo).
- `support.js`: runtime necesario para abrir el prototipo en el navegador (no forma parte de la implementación).
