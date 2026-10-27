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
 * @lastModified 2026-10-25
 * @version      v1.1.0
 *
 * @license      Propietario - BigBitByte / Institución Educativa
 *               Todos los derechos reservados.
 *
 * @description  Carga masiva de recursos con el estilo del prototipo: zona
 *               de selección de archivo, formato admitido y errores por fila.
 *
 * @requisitos   RF-012
 *
 * @see          docs/requisitos/BBB_CU008_ImportarCatalogo_v1.0.md; DT-028
 * ============================================================================
--}}
@extends('layouts.app')

@section('titulo', 'Importar CSV')
@section('ruta', 'Recursos')

@section('contenido')
    <div class="cuadricula-380">
        <form method="POST" action="{{ route('recursos.importar.store') }}" enctype="multipart/form-data"
              class="tarjeta tarjeta--relleno pila--16 titulos" novalidate>
            @csrf
            <div class="titulos">
                <h2 class="tarjeta__titulo">Carga masiva de recursos</h2>
                <span class="tarjeta__subtitulo">Si alguna fila tiene errores no se importa ninguna.</span>
            </div>
            <label for="archivo" class="zona-archivo">
                <span class="icono icono--40 icono--azul" aria-hidden="true">upload_file</span>
                <span class="texto-15 negrita">Selecciona tu archivo CSV</span>
                <span class="texto-13 texto-sec">
                    UTF-8 separado por comas · máx. 2 MB y {{ number_format($maximoFilas, 0, '.', ',') }} filas
                </span>
                <input type="file" id="archivo" name="archivo" class="control" accept=".csv,text/csv" required
                       @error('archivo') aria-invalid="true" aria-describedby="archivo-error" @enderror>
            </label>
            @error('archivo')
                <div id="archivo-error" class="alerta alerta--error" role="alert">
                    <span class="icono" aria-hidden="true">error</span>
                    <ul class="alerta__lista">
                        @foreach ($errors->get('archivo') as $mensaje)
                            <li>{{ $mensaje }}</li>
                        @endforeach
                    </ul>
                </div>
            @enderror
            <div class="pie-pasos">
                <a href="{{ route('recursos.index') }}" class="boton boton--fantasma boton--grande">Cancelar</a>
                <button type="submit" class="boton boton--primario boton--grande">
                    <span class="icono" aria-hidden="true">upload_file</span>Importar
                </button>
            </div>
        </form>

        <section class="tarjeta tarjeta--relleno pila--12 titulos" aria-labelledby="titulo-formato">
            <h2 id="titulo-formato" class="tarjeta__titulo">Formato del archivo</h2>
            <span class="campo__etiqueta">Encabezado (primera fila, en este orden)</span>
            <code class="bloque-codigo">{{ $encabezado }}</code>
            <div class="reglas">
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">category</span><span><code class="mono">tipo_recurso</code>:
                    <code class="mono">equipo_fisico</code> o <code class="mono">espacio</code>.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">apartment</span><span><code class="mono">nombre_area</code>
                    debe coincidir con un área activa.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">tag</span><span>Inventario y serie no se pueden repetir en el
                    archivo ni en la institución.</span>
                </div>
                <div class="reglas__item">
                    <span class="icono" aria-hidden="true">event</span><span><code class="mono">anio_adquisicion</code>
                    es opcional, entre 1990 y {{ now()->year }}.</span>
                </div>
            </div>
        </section>
    </div>
@endsection
