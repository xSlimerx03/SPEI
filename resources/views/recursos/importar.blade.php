{{--
 * ============================================================================
 * SPEI - Sistema de Préstamo de Equipo Institucional
 * ============================================================================
 *
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Módulo 4.2: Gestión de la Estructura Organizacional y Catálogo
 * @file         importar.blade.php
 * @path         resources/views/recursos/importar.blade.php
 *
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Carga masiva de recursos: formato admitido y archivo CSV.
 *
 * @requisitos   RF-012
 *
 * @see          docs/requisitos/BBB_CU008_ImportarCatalogo_v1.0.md (paso 1)
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Importar catálogo')

@section('contenido')
    <div class="tarjeta contenedor-formulario">
        <h2 class="tarjeta__titulo">Formato del archivo</h2>
        <ul>
            <li>Archivo <strong>.csv</strong> en UTF-8 separado por comas, máximo 2 MB y {{ number_format($maximoFilas, 0, '.', ' ') }} filas.</li>
            <li>Primera fila (encabezado), en este orden:<br><code>{{ $encabezado }}</code></li>
            <li><code>tipo_recurso</code>: <code>equipo_fisico</code> o <code>espacio</code>.</li>
            <li><code>nombre_area</code> debe coincidir con un área activa.</li>
            <li>Si alguna fila tiene errores no se importa ninguna.</li>
        </ul>

        <form method="POST" action="{{ route('recursos.importar.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="campo">
                <label for="archivo" class="campo__etiqueta">Archivo CSV</label>
                <input type="file" id="archivo" name="archivo" class="control" accept=".csv,text/csv" required
                       style="padding-top: 10px;">
            </div>
            <div class="acciones">
                <button type="submit" class="boton">
                    <span class="icono icono--boton" aria-hidden="true">upload_file</span>Importar
                </button>
                <a href="{{ route('recursos.index') }}" class="boton boton--secundario">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
