{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.1: Gestión SaaS y Suscripción Institucional
 * @file         cambiar-plan.blade.php
 * @path         resources/views/suscripcion/cambiar-plan.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Formulario de cambio de plan. Variables: institucion, planes,
 *               accion (URL del PUT).
 *
 * @requisitos   RF-003, RN-010
 *
 * @see          docs/requisitos/BBB_CU002_AdministrarSuscripcion_v1.0.md (pasos 2–4)
 * ============================================================================
--}}
<section class="tarjeta" aria-labelledby="titulo-plan">
    <h2 id="titulo-plan" class="tarjeta__titulo">Cambiar plan</h2>
    <p class="texto-secundario">El plan nuevo debe admitir las áreas y los recursos ya registrados.</p>
    <form method="POST" action="{{ $accion }}" class="fila-campos">
        @csrf
        @method('PUT')
        <div class="campo">
            <label for="plan" class="campo__etiqueta">Plan</label>
            <select id="plan" name="plan" class="control">
                @foreach ($planes as $plan)
                    <option value="{{ $plan->clave->value }}" @selected($plan->clave === $institucion->plan_suscripcion)>
                        {{ $plan->nombre }} · {{ $plan->limite_areas }} áreas · {{ number_format($plan->limite_recursos) }} recursos
                    </option>
                @endforeach
            </select>
        </div>
        <div class="campo" style="justify-content: flex-end;">
            <button type="submit" class="boton">
                <span class="icono icono--boton" aria-hidden="true">save</span>Guardar
            </button>
        </div>
    </form>
</section>
