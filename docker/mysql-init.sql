-- ============================================================================
-- SPEI - Sistema de Préstamo de Equipo Institucional
-- ============================================================================
-- @project      SPEI (Sistema de Préstamo de Equipo Institucional)
-- @module       Infraestructura
-- @file         mysql-init.sql
-- @path         docker/mysql-init.sql
-- @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
-- @date         2026-10-18
-- @lastModified 2026-10-18
-- @version      v1.0.0
-- @license      Propietario - BigBitByte / Institución Educativa
--               Todos los derechos reservados.
-- @description  Crea la base spei_testing para las pruebas Pest sobre MySQL
--               real (MD 11.1) y da permisos al usuario de la aplicación.
--               Solo se ejecuta al inicializar el volumen de desarrollo.
-- @requisitos   RNF-005
-- @see          Manual de Desarrollo v1.2, Tabla 23
-- ============================================================================

CREATE DATABASE IF NOT EXISTS spei_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON spei_testing.* TO 'spei_user'@'%';
FLUSH PRIVILEGES;
