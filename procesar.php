<?php
require_once 'plantilla_factura.php';
session_start();

// Array para errores
$errores = [];

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Este archivo solo acepta peticiones POST.");
}

// ==================== DATOS DEL EMISOR ====================
$campos_emisor = [
    'nombre_emisor','nit_emisor','nrc_emisor','actividad_economica',
    'direccion_emisor','telefono_emisor','correo_emisor','nombre_comercial','establecimiento'
];
$emisor = [];
foreach ($campos_emisor as $campo) {
    $emisor[$campo] = trim($_POST[$campo] ?? '');
}

// Validaciones emisor
if (!empty($emisor['correo_emisor']) && !filter_var($emisor['correo_emisor'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Correo del emisor inválido.";
}
if (!empty($emisor['nit_emisor']) && !preg_match('/^\d{4}-\d{6}-\d{3}-\d$/', $emisor['nit_emisor'])) {
    $errores[] = "El NIT debe tener el formato ####-######-###-#";
}
if (!empty($emisor['nrc_emisor']) && !preg_match('/^\d{6,8}$/', $emisor['nrc_emisor'])) {
    $errores[] = "El NRC debe ser numérico (6-8 dígitos).";
}

// ==================== DATOS DEL CLIENTE ====================
$campos_cliente = [
    'nombre_cliente','documento_cliente','direccion_cliente','telefono_cliente',
    'correo_cliente','nombre_comercial_cliente'
];
$cliente = [];
foreach ($campos_cliente as $campo) {
    $cliente[$campo] = trim($_POST[$campo] ?? '');
}
if (!empty($cliente['correo_cliente']) && !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Correo del cliente inválido.";
}

// ==================== TIPO DE DOCUMENTO ====================
$tipo_documento = $_POST['tipo_documento'] ?? '';
if (!in_array($tipo_documento, ['01','03'])) {
    $errores[] = "Tipo de documento inválido.";
}

// ==================== ITEMS ====================
$items_raw = $_POST['items'] ?? [];
if (empty($items_raw)) {
    $errores[] = "Debe agregar al menos un ítem a la factura.";
}

// ==================== MOSTRAR ERRORES ====================
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['datos'] = $_POST;
    header("Location: formulario_factura.php");
    exit;
}

// ==================== PROCESAR ITEMS ====================
$items_procesados = [];
$subtotales = ['no_sujeta'=>0,'exenta'=>0,'gravada'=>0];
foreach ($items_raw as $index=>$item) {
    $cantidad = floatval($item['cantidad'] ?? 0);
    $precio_unitario = floatval($item['precio_unitario'] ?? 0);
    $categoria = $item['categoria'] ?? 'gravada';
    $codigo = htmlspecialchars($item['codigo'] ?? '');
    $descripcion = htmlspecialchars($item['descripcion'] ?? '');
    $venta = $cantidad * $precio_unitario;

    $items_procesados[] = [
        'numero'=>$index+1,
        'cantidad'=>$cantidad,
        'codigo'=>$codigo,
        'descripcion'=>$descripcion,
        'venta_no_sujeta'=> $categoria==='no_sujeta'? $venta : 0,
        'venta_exenta'=> $categoria==='exenta'? $venta : 0,
        'venta_gravada'=> $categoria==='gravada'? $venta : 0
    ];
    $subtotales[$categoria] += $venta;
}

// ==================== CÁLCULOS TOTALES ====================
$suma_no_sujeta = $subtotales['no_sujeta'];
$suma_exenta = $subtotales['exenta'];
$suma_gravada = $subtotales['gravada'];
$iva = $suma_gravada*0.13;
$total_general = $suma_no_sujeta+$suma_exenta+$suma_gravada+$iva;

// ==================== DATOS PARA PLANTILLA ====================
$datos_factura = [
    'tipo_documento'=>$tipo_documento,
    'numero_documento'=>date('YmdHis'),
    'fecha'=>date('d/m/Y'),
    'emisor'=>$emisor,
    'cliente'=>$cliente,
    'items'=>$items_procesados,
    'calculos'=>[
        'suma_no_sujeta'=>$suma_no_sujeta,
        'suma_exenta'=>$suma_exenta,
        'suma_gravada'=>$suma_gravada,
        'iva'=>$iva,
        'iva_retenido'=>0,
        'total_general'=>$total_general
    ]
];

// ==================== GENERAR PDF ====================
generarPDF($datos_factura);
