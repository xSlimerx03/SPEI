# Incremento MVP — SPEI

Fecha: 18/10/2026. Responsable: Raúl Treviño Licona. Estado: en implementación.

El usuario solicita un MVP utilizable y exige fidelidad visual a los manuales. Este incremento
no sustituye el alcance de la liberación 1.0 de 58 RF.

## Recorrido de aceptación

1. Ver planes activos, contratar una institución e iniciar sesión.
2. Invitar un administrador, completar registro y administrar áreas e inventario.
3. Dar de alta por invitación alumnos, docentes y asistentes de la institución.
4. Consultar catálogo, solicitar uno o varios recursos de una misma área y reservarlos sin duplicación.
5. Mostrar un QR temporal del solicitante; el operador lo escanea o captura su código para entregar.
6. Confirmar devolución con un segundo QR, registrar condición e incidencia y liberar el recurso.
7. Consultar historial, notificaciones y comprobante imprimible; exportar el historial permitido.
8. Cancelar antes de entregar; liberar reservas no recogidas dentro de la tolerancia.

## Criterios transversales

- Logo original extraído del Manual de Programación; paleta, tamaños y layouts de sus tablas 48–78.
- No crear datos de demostración dentro de instituciones reales ni sustituir planes modificados.
- Aislamiento por institución, autorización por rol y área, validación de servidor y auditoría.
- Reservas serializadas por institución mediante Redis y transacciones MySQL con bloqueos.
- QR de un solo uso de 45 segundos; token almacenado con hash, sin enviarlo a servicios externos.
- Pruebas funcionales del recorrido y rechazos por permisos, vigencia, duplicación y estado.

## Límites explícitos del MVP

El MVP reserva el recurso completo desde la solicitud hasta su cancelación o devolución; no ofrece
reservas futuras encadenadas del mismo recurso ni arbitraje de prioridad docente sobre otra reserva.
Solicitudes de distintas áreas se capturan por separado. El comprobante se imprime o guarda como PDF
desde el navegador. Quedan para incrementos posteriores: sincronización sin conexión, sanciones
automáticas, arbitraje de prioridad, CSV de usuarios, diálogo previo, PDF generado en servidor y
reportes analíticos avanzados. Las interfaces no ofrecerán acciones todavía inexistentes.

Los datos nuevos se incorporan mediante migraciones aditivas; los datos existentes se conservan.
