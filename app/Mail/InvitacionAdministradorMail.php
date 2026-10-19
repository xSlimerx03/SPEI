<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         InvitacionAdministradorMail.php
 * @path         app/Mail/InvitacionAdministradorMail.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Correo en cola con el enlace firmado para que el administrador
 *               del sistema invitado complete su registro.
 *
 * @requisitos   RF-008, RNF-006
 *
 * @see          docs/requisitos/BBB_CU004_InvitarAdministrador_v1.0.md; DT-025
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
 * Invitación para completar el registro.
 *
 * @package App\Mail
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class InvitacionAdministradorMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $nombreInvitado,
        public string $nombreInstitucion,
        public string $enlace,
    ) {}

    /**
     * Asunto del correo.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitación al SPEI de ' . $this->nombreInstitucion);
    }

    /**
     * Vista del cuerpo del correo.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(view: 'correos.invitacion-administrador');
    }
}
