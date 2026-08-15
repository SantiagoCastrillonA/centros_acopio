<?php

return [

    /*
     * Responsable del tratamiento de datos personales.
     *
     * La Ley 1581 de 2012 exige identificarlo con nombre y datos de contacto
     * reales en la politica de tratamiento. Mientras estos valores esten
     * vacios, /privacidad muestra una advertencia y el formulario de
     * voluntarios no debe abrirse al publico.
     *
     * Se llenan en el .env del servidor, no aqui.
     */
    'responsable' => [
        'nombre' => env('RESPONSABLE_NOMBRE'),
        'documento' => env('RESPONSABLE_DOCUMENTO'),
        'direccion' => env('RESPONSABLE_DIRECCION'),
        'correo' => env('RESPONSABLE_CORREO'),
        'telefono' => env('RESPONSABLE_TELEFONO'),
    ],

];
