<?php

/**
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.4: Solicitudes y Reservas
 * @file         SolicitarPrestamoRequest.php
 * @path         app/Http/Requests/SolicitarPrestamoRequest.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Validación de la nueva solicitud del asistente de tres pasos:
 *               recursos (1 a 10), día, hora de inicio y fin dentro del horario
 *               operativo, motivo y aceptación del reglamento.
 *
 * @requisitos   RF-022, RF-023, RF-024, RN-004
 *
 * @see          docs/plan/BBB_MVP_v1.0.md; app/Services/PrestamoService.php; DT-028
 * ============================================================================
 */

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Usuario;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * Datos de una reserva.
 *
 * @package App\Http\Requests
 * @author Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @since v1.0.0
 */
class SolicitarPrestamoRequest extends FormRequest
{
    /**
     * Solo alumnos y docentes solicitan préstamos.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $usuario = $this->user();

        return $usuario instanceof Usuario && $usuario->rol->esUsuarioFinal();
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recursos' => ['required', 'array', 'min:1', 'max:10'],
            'recursos.*' => ['required', 'integer', 'distinct'],
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . today()->toDateString(),
                'before_or_equal:' . today()->addDays(60)->toDateString(),
            ],
            'hora_inicio' => ['required', 'date_format:H:i', $this->validarHorarioFuturo(...)],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio', 'before_or_equal:22:00'],
            'motivo' => ['required', 'string', 'min:5', 'max:1000'],
            'acepta_reglamento' => ['accepted'],
        ];
    }

    /**
     * Mensajes personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recursos.required' => 'Seleccione al menos un recurso.',
            'recursos.max' => 'Seleccione entre uno y diez recursos de una misma área.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'hora_fin.before_or_equal' => 'Elija una hora dentro del horario operativo (7:00 a 22:00).',
            'acepta_reglamento.accepted' => 'Debe aceptar el reglamento de préstamo.',
        ];
    }

    /**
     * Nombres de los campos en los mensajes.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fecha' => 'día del préstamo',
            'hora_inicio' => 'hora de inicio',
            'hora_fin' => 'hora de fin',
            'motivo' => 'motivo de uso',
        ];
    }

    /**
     * Datos en el formato que espera PrestamoService::solicitar().
     *
     * @return array{recursos: list<int|string>, fecha_hora_inicio: string, fecha_hora_fin: string, motivo: string}
     */
    public function datosServicio(): array
    {
        $datos = $this->validated();

        return [
            'recursos' => array_values($datos['recursos']),
            'fecha_hora_inicio' => $datos['fecha'] . ' ' . $datos['hora_inicio'],
            'fecha_hora_fin' => $datos['fecha'] . ' ' . $datos['hora_fin'],
            'motivo' => trim($datos['motivo']),
        ];
    }

    /**
     * Rechaza horarios de inicio que ya pasaron y los que salen del horario operativo (7:00 a 22:00).
     *
     * @param  string  $atributo  Campo validado.
     * @param  mixed  $valor  Hora capturada.
     * @param  Closure(string): void  $fallar  Reporta el error.
     * @return void
     */
    private function validarHorarioFuturo(string $atributo, mixed $valor, Closure $fallar): void
    {
        $hora = (string) $valor;

        if ($hora < '07:00' || $hora > '22:00') {
            $fallar('Elija una hora dentro del horario operativo (7:00 a 22:00).');

            return;
        }

        $fecha = (string) $this->input('fecha');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1
            && Carbon::parse($fecha . ' ' . $hora)->lt(now()->startOfMinute())) {
            $fallar('Elija un horario futuro.');
        }
    }
}
