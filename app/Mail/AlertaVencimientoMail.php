<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         AlertaVencimientoMail.php
 * @path         app/Mail/AlertaVencimientoMail.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Correo en cola que avisa al administrador institucional del
 *               próximo vencimiento de la suscripción.
 *
 * @requisitos   RF-005, RNF-006
 *
 * @see          app/Services/AlertaVencimientoService.php
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de vencimiento de la suscripción.
 *
 * @package App\Mail
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AlertaVencimientoMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $nombreDestinatario,
        public string $mensaje,
    ) {}

    /**
     * Asunto del correo.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Su suscripción al SPEI está por vencer');
    }

    /**
     * Vista del cuerpo del correo.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(view: 'correos.alerta-vencimiento');
    }
}
