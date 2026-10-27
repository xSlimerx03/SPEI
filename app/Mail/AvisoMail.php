<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.7: Notificaciones y Recordatorios
 * @file         AvisoMail.php
 * @path         app/Mail/AvisoMail.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-27
 * @lastModified 2026-10-27
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Correo de aviso de los eventos del préstamo, recordatorios y
 *               sanciones (RF-048). Se encola en Redis y solo se envía si la
 *               transacción que lo origina se confirma.
 *
 * @requisitos   RF-045, RF-046, RF-048, RF-050, RNF-004
 *
 * @see          CU-021
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
 * Aviso por correo electrónico.
 *
 * @package App\Mail
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class AvisoMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Recibe los datos del correo.
     *
     * @param  string  $nombreDestinatario  Nombre de quien recibe el aviso.
     * @param  string  $titulo  Asunto y encabezado.
     * @param  string  $mensaje  Texto del aviso.
     * @param  string|null  $enlace  Pantalla del SPEI relacionada, si existe.
     */
    public function __construct(
        public string $nombreDestinatario,
        public string $titulo,
        public string $mensaje,
        public ?string $enlace = null,
    ) {
        // Un aviso de una operación revertida no debe salir.
        $this->afterCommit();
    }

    /**
     * Asunto del correo.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->titulo . ' · SPEI');
    }

    /**
     * Vista del cuerpo del correo.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(view: 'correos.aviso');
    }
}
