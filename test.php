<?php
//Para probar el plantilla factura 
require 'plantilla_factura.php';

$datos = [
    'tipo_documento' => '01',
    'emisor' => [
        'nombre_emisor' => 'Empresa Demo',
        'nit_emisor' => '0000-000000-000-0',
        'nrc_emisor' => '123456-7',
        'actividad_economica' => 'Ventas',
        'direccion_emisor' => 'San Salvador',
        'telefono_emisor' => '2222-2222',
        'correo_emisor' => 'demo@correo.com',
    ],
    'cliente' => [
        'nombre_cliente' => 'Juan Pérez',
        'documento_cliente' => 'DUI 00000000-0',
        'direccion_cliente' => 'Santa Ana',
        'telefono_cliente' => '7777-7777',
        'correo_cliente' => 'cliente@correo.com',
    ],
    'items' => [
        [
            'numero' => 1,
            'cantidad' => 2,
            'codigo' => 'A01',
            'descripcion' => 'Producto de prueba',
            'venta_no_sujeta' => 0,
            'venta_exenta' => 0,
            'venta_gravada' => 10.00,
        ]
    ],
    'calculos' => [
        'suma_no_sujeta' => 0,
        'suma_exenta' => 0,
        'suma_gravada' => 20.00,
        'iva' => 2.60,
        'iva_retenido' => 0,
        'total_general' => 22.60
    ]
];

generarPDF($datos);
