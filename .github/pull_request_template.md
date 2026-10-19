## Requisito
<!-- RF / RNF / RN / RS y CU-01 asociados. Ej.: RF-022, CU-005 -->

## Resumen
<!-- Qué cambia y por qué. -->

## Pruebas
<!-- CP-01 ejecutados, resultado de Pest y % de cobertura. -->

## Migraciones
<!-- Migraciones nuevas, formato DDL-XX asociado y verificación de rollback. "Ninguna" si no aplica. -->

## Impacto multi-tenant
<!-- Cómo se garantiza el aislamiento por id_institucion y qué prueba de acceso cruzado lo demuestra. -->

## Evidencia
<!-- Enlace al RED-01 del spei-auditor, salidas de CI, capturas. -->

## Riesgos
<!-- Riesgos residuales y plan de reversión. -->

## Lista de verificación (MD 10.2 y 10.4)
- [ ] Rama `feature/RF-XXX-*`, `fix/DEF-XXX-*`, `hotfix/vX.Y.Z-*` o `docs/BBB-*`
- [ ] Cabeceras de prólogo completas y `@lastModified` actualizado
- [ ] Pint, PHPStan 6 y `composer audit` sin errores
- [ ] Pruebas en verde y cobertura ≥ 80 %
- [ ] Sin secretos, archivos temporales ni datos personales
- [ ] RT-01 y diccionario de datos actualizados
- [ ] Revisión RED-01 del `spei-auditor` sin hallazgos bloqueantes
