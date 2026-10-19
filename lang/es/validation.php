<?php

/**
 * @project      SPEI (Sistema de Préstamo de Equipo Institucional)
 * @module       Interfaz web
 * @file         validation.php
 * @path         lang/es/validation.php
 * @author       Raúl Treviño Licona <raul.trevino@institucion.edu.mx>
 * @date         2026-10-18
 * @lastModified 2026-10-18
 * @version      v1.0.0
 * @license      Propietario - BigBitByte / Institución Educativa
 * @description  Mensajes de validación en español para los formularios del SPEI.
 * @requisitos   RNF-008, RNF-001
 * @see          docs/requisitos/BBB_CU001_ContratarSuscripcion_v1.0.md
 */

declare(strict_types=1);

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El valor de :attribute ya está registrado.',
    'exists' => 'Seleccione un valor válido para :attribute.',
    'regex' => 'El formato de :attribute no es válido.',
    'ends_with' => 'El campo :attribute debe terminar en :values.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'in' => 'Seleccione un valor válido para :attribute.',
    'enum' => 'Seleccione un valor válido para :attribute.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'mimes' => 'El archivo :attribute debe ser de tipo :values.',
    'mimetypes' => 'El archivo :attribute debe ser de tipo :values.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'max' => [
        'string' => 'El campo :attribute no debe superar :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file' => 'El archivo :attribute no debe superar :max kilobytes.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'password' => [
        'letters' => 'El campo :attribute debe incluir al menos una letra.',
        'numbers' => 'El campo :attribute debe incluir al menos un número.',
        'mixed' => 'El campo :attribute debe incluir mayúsculas y minúsculas.',
        'symbols' => 'El campo :attribute debe incluir al menos un símbolo.',
        'uncompromised' => 'Elija otra contraseña; esta aparece en una filtración de datos.',
    ],
    'attributes' => [],
    'custom' => [],
];
