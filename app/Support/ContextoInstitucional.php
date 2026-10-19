<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Núcleo multi-tenant
 * @file         ContextoInstitucional.php
 * @path         app/Support/ContextoInstitucional.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Guarda el tenant de la petición en curso. TenantMiddleware lo
 *               establece y AmbitoInstitucionScope lo usa para filtrar cada
 *               consulta por id_institucion.
 *
 * @requisitos   RS-006
 *
 * @see          Manual de Desarrollo v1.2, sección 5.3; DT-008
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Support;

/**
 * Contenedor del identificador de institución activo durante la petición.
 *
 * @package App\Support
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class ContextoInstitucional
{
    private ?int $idInstitucion = null;

    /**
     * Fija la institución de la petición en curso.
     *
     * @param  int  $idInstitucion  Identificador del tenant.
     * @return void
     */
    public function establecer(int $idInstitucion): void
    {
        $this->idInstitucion = $idInstitucion;
    }

    /**
     * Devuelve la institución activa o null si no hay tenant (superadmin o consola).
     *
     * @return int|null
     */
    public function obtenerIdInstitucion(): ?int
    {
        return $this->idInstitucion;
    }

    /**
     * Indica si hay una institución activa.
     *
     * @return bool
     */
    public function existe(): bool
    {
        return $this->idInstitucion !== null;
    }

    /**
     * Elimina la institución activa.
     *
     * @return void
     */
    public function limpiar(): void
    {
        $this->idInstitucion = null;
    }
}
