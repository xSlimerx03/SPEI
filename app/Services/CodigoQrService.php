<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.5: Entrega y Devolución
 * @file         CodigoQrService.php
 * @path         app/Services/CodigoQrService.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-23
 * @lastModified 2026-10-25
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Dibuja en el servidor el SVG del código QR de custodia con
 *               BaconQrCode; el código nunca se envía a servicios externos.
 *
 * @requisitos   RF-034, RF-036, RNF-001
 *
 * @see          docs/plan/BBB_MVP_v1.0.md (QR de un solo uso de 45 s)
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Generación local de códigos QR.
 *
 * @package App\Services
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class CodigoQrService
{
    /**
     * Devuelve el SVG del código, sin la declaración XML para incrustarlo en HTML.
     *
     * @param  string  $codigo  Texto a codificar.
     * @param  int  $tamano  Lado en píxeles.
     * @return string
     */
    public function dibujar(string $codigo, int $tamano = 250): string
    {
        $escritor = new Writer(new ImageRenderer(new RendererStyle($tamano, 0), new SvgImageBackEnd));
        $svg = $escritor->writeString($codigo);

        return (string) preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
    }
}
