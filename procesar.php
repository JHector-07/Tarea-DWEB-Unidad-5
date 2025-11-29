<?php
require_once 'plantilla_factura.php';

// Array para almacenar errores de validación
$errores = [];

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Este archivo solo acepta peticiones POST.");
}

// ==================== VALIDACIÓN DE CAMPOS OBLIGATORIOS ====================

// 1. TIPO DE DOCUMENTO
if (empty($_POST['tipo_documento'])) {
    $errores[] = "El tipo de documento es obligatorio.";
} else {
    $tipo_documento = $_POST['tipo_documento'];
    if (!in_array($tipo_documento, ['01', '03'])) {
        $errores[] = "Tipo de documento inválido. Debe ser 01 o 03.";
    }
}

// 2. DATOS DEL EMISOR
$emisor = [];
$campos_emisor = [
    'nombre_emisor' => 'Nombre/Razón Social del Emisor',
    'nit_emisor' => 'NIT del Emisor',
    'nrc_emisor' => 'NRC del Emisor',
    'actividad_economica' => 'Actividad Económica',
    'direccion_emisor' => 'Dirección del Emisor',
    'telefono_emisor' => 'Teléfono del Emisor',
    'correo_emisor' => 'Correo del Emisor',
    'nombre_comercial' => 'Nombre Comercial',
    'establecimiento' => 'Establecimiento'
];

foreach ($campos_emisor as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[] = "$nombre es obligatorio.";
    } else {
        $emisor[$campo] = trim($_POST[$campo]);
    }
}

// Validar formato NIT (####-######-###-#)
if (!empty($emisor['nit_emisor']) && !preg_match('/^\d{4}-\d{6}-\d{3}-\d$/', $emisor['nit_emisor'])) {
    $errores[] = "El NIT debe tener el formato ####-######-###-#";
}

// Validar formato NRC (######-# o similar)
if (!empty($emisor['nrc_emisor']) && !preg_match('/^\d{6,8}(-\d)?$/', $emisor['nrc_emisor'])) {
    $errores[] = "El NRC debe ser numérico (6-8 dígitos).";
}

// Validar correo electrónico
if (!empty($emisor['correo_emisor']) && !filter_var($emisor['correo_emisor'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo del emisor no es válido.";
}

// 3. DATOS DEL CLIENTE
$cliente = [];
$campos_cliente = [
    'nombre_cliente' => 'Nombre/Razón Social del Cliente',
    'documento_cliente' => 'Documento del Cliente (NIT o DUI)',
    'direccion_cliente' => 'Dirección del Cliente',
    'telefono_cliente' => 'Teléfono del Cliente',
    'correo_cliente' => 'Correo del Cliente',
    'nombre_comercial_cliente' => 'Nombre Comercial del Cliente'
];

foreach ($campos_cliente as $campo => $nombre) {
    if (empty($_POST[$campo])) {
        $errores[] = "$nombre es obligatorio.";
    } else {
        $cliente[$campo] = trim($_POST[$campo]);
    }
}

// Validar correo del cliente
if (!empty($cliente['correo_cliente']) && !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo del cliente no es válido.";
}

// 4. VALIDACIÓN DE ITEMS
if (empty($_POST['items']) || !is_array($_POST['items'])) {
    $errores[] = "Debe agregar al menos un ítem a la factura.";
} else {
    $items = $_POST['items'];
    
    if (count($items) < 1) {
        $errores[] = "Debe agregar al menos un ítem a la factura.";
    }
    
    foreach ($items as $index => $item) {
        $num = $index + 1;
        
        // Validar cantidad
        if (empty($item['cantidad']) || !is_numeric($item['cantidad']) || $item['cantidad'] <= 0) {
            $errores[] = "Ítem #$num: La cantidad debe ser un número mayor a 0.";
        }
        
        // Validar código
        if (empty($item['codigo'])) {
            $errores[] = "Ítem #$num: El código es obligatorio.";
        }
        
        // Validar descripción
        if (empty($item['descripcion'])) {
            $errores[] = "Ítem #$num: La descripción es obligatoria.";
        }
        
        // Validar precio unitario
        if (empty($item['precio_unitario']) || !is_numeric($item['precio_unitario']) || $item['precio_unitario'] <= 0) {
            $errores[] = "Ítem #$num: El precio unitario debe ser un número mayor a 0.";
        }
        
        // Validar categoría (No Sujeta, Exenta o Gravada)
        if (empty($item['categoria'])) {
            $errores[] = "Ítem #$num: Debe seleccionar una categoría (No Sujeta, Exenta o Gravada).";
        } else if (!in_array($item['categoria'], ['no_sujeta', 'exenta', 'gravada'])) {
            $errores[] = "Ítem #$num: Categoría inválida.";
        }
    }
}

// ==================== MOSTRAR ERRORES O PROCESAR ====================

if (!empty($errores)) {
    echo "<h2>Errores de Validación:</h2>";
    echo "<ul style='color: red;'>";
    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<br><a href='formulario.php'>Volver al formulario</a>";
    exit;
}

// ==================== CÁLCULOS ====================

$subtotales = [
    'no_sujeta' => 0,
    'exenta' => 0,
    'gravada' => 0
];

$items_procesados = [];

foreach ($items as $item) {
    $cantidad = floatval($item['cantidad']);
    $precio_unitario = floatval($item['precio_unitario']);
    $categoria = $item['categoria'];
    
    // Calcular venta según categoría
    $venta = $cantidad * $precio_unitario;
    
    $items_procesados[] = [
        'numero' => count($items_procesados) + 1,
        'cantidad' => $cantidad,
        'codigo' => htmlspecialchars($item['codigo']),
        'descripcion' => htmlspecialchars($item['descripcion']),
        'precio_unitario' => $precio_unitario,
        'categoria' => $categoria,
        'venta_no_sujeta' => $categoria === 'no_sujeta' ? $venta : 0,
        'venta_exenta' => $categoria === 'exenta' ? $venta : 0,
        'venta_gravada' => $categoria === 'gravada' ? $venta : 0
    ];
    
    $subtotales[$categoria] += $venta;
}

// SUMAS
$suma_no_sujeta = $subtotales['no_sujeta'];
$suma_exenta = $subtotales['exenta'];
$suma_gravada = $subtotales['gravada'];

// Total Exentas
$total_exentas = $suma_exenta;

// IVA 13% (solo sobre ventas gravadas)
$iva = $suma_gravada * 0.13;

// Sub-total
$subtotal = $suma_no_sujeta + $suma_exenta + $suma_gravada + $iva;

// IVA Retenido (solo para CCF si el cliente cumple condición)
$iva_retenido = 0;
if ($tipo_documento === '03' && !empty($_POST['retiene_iva']) && $_POST['retiene_iva'] === '1') {
    $iva_retenido = $iva; // 100% del IVA
}

// Total No Sujetas
$total_no_sujetas = $suma_no_sujeta;

// TOTAL GENERAL
$total_general = $subtotal - $iva_retenido;

// ==================== GENERAR PDF ====================

$datos_factura = [
    'tipo_documento' => $tipo_documento,
    'emisor' => $emisor,
    'cliente' => $cliente,
    'items' => $items_procesados,
    'calculos' => [
        'suma_no_sujeta' => $suma_no_sujeta,
        'suma_exenta' => $suma_exenta,
        'suma_gravada' => $suma_gravada,
        'total_exentas' => $total_exentas,
        'iva' => $iva,
        'subtotal' => $subtotal,
        'iva_retenido' => $iva_retenido,
        'total_no_sujetas' => $total_no_sujetas,
        'total_general' => $total_general
    ]
];

// Llamar a la función que genera el PDF (definida en plantilla_factura.php)
generarPDF($datos_factura);

