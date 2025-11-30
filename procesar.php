<?php
/**
 * procesar.php - Procesador de formularios de Factura y CCF
 * 
 * Este archivo valida los datos del formulario y genera los PDFs
 * correspondientes usando la librería DOMPDF.
 */

// Cargar las plantillas
require_once "plantilla_factura.php";
require_once "plantilla_ccf.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo_documento = $_POST['tipo_documento']; // 01 o 03
    $errores = [];

    // ================================================
    // VALIDACIONES GENERALES
    // ================================================

    // Validar NIT del cliente (formato: ####-######-###-#)
    if (empty($_POST['documento_cliente']) || !preg_match('/^\d{4}-\d{6}-\d{3}-\d$/', $_POST['documento_cliente'])) {
        $errores[] = "El NIT debe tener el formato ####-######-###-# (ejemplo: 1234-567890-123-4)";
    }

    // Validar correo del cliente
    if (empty($_POST['correo_cliente']) || !filter_var($_POST['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo del cliente inválido.";
    }

    // Validar campos obligatorios del emisor
    $campos_emisor_obligatorios = ['nombre_emisor', 'nit_emisor', 'nrc_emisor', 'actividad_economica', 'direccion_emisor', 'telefono_emisor', 'correo_emisor'];
    foreach ($campos_emisor_obligatorios as $campo) {
        if (empty($_POST[$campo])) {
            $errores[] = "El campo '{$campo}' del emisor es obligatorio.";
        }
    }

    // Validar campos obligatorios del cliente
    $campos_cliente_obligatorios = ['nombre_cliente', 'documento_cliente', 'direccion_cliente', 'telefono_cliente', 'correo_cliente'];
    foreach ($campos_cliente_obligatorios as $campo) {
        if (empty($_POST[$campo])) {
            $errores[] = "El campo '{$campo}' del cliente es obligatorio.";
        }
    }

    // Si es CCF, validar NRC obligatorio del cliente
    if ($tipo_documento === "03") {
        if (empty($_POST['nrc_cliente']) || !preg_match('/^\d{6,8}$/', $_POST['nrc_cliente'])) {
            $errores[] = "Para CCF, el NRC del cliente debe ser numérico (6-8 dígitos).";
        }
    }

    // Validar que haya al menos 1 ítem
    if (empty($_POST['items']) || count($_POST['items']) === 0) {
        $errores[] = "Debe agregar al menos 1 ítem a la factura.";
    } else {
        // Validar que cada ítem tenga cantidad y precio válidos
        foreach ($_POST['items'] as $i => $item) {
            $cantidad = floatval($item['cantidad'] ?? 0);
            $precio = floatval($item['precio_unitario'] ?? 0);

            if (empty($item['descripcion'])) {
                $errores[] = "La descripción del ítem " . ($i + 1) . " es obligatoria.";
            }
            if ($cantidad <= 0 || !is_numeric($item['cantidad'])) {
                $errores[] = "La cantidad del ítem " . ($i + 1) . " debe ser un número positivo.";
            }
            if ($precio <= 0 || !is_numeric($item['precio_unitario'])) {
                $errores[] = "El precio unitario del ítem " . ($i + 1) . " debe ser un número positivo.";
            }
        }
    }

    // Si hay errores → regresar al formulario
    if (!empty($errores)) {
        session_start();
        $_SESSION['errores'] = $errores;
        $_SESSION['datos'] = $_POST;
        header("Location: formulario_factura.php");
        exit;
    }

    // ================================================
    // CAPTURAR DATOS DEL EMISOR
    // ================================================
    $datos['emisor'] = [
        'nombre_emisor'        => $_POST['nombre_emisor'],
        'nit_emisor'           => $_POST['nit_emisor'],
        'nrc_emisor'           => $_POST['nrc_emisor'],
        'actividad_economica'  => $_POST['actividad_economica'],
        'direccion_emisor'     => $_POST['direccion_emisor'],
        'telefono_emisor'      => $_POST['telefono_emisor'],
        'correo_emisor'        => $_POST['correo_emisor'],
        'nombre_comercial'     => $_POST['nombre_comercial'] ?? '',
        'establecimiento'      => $_POST['establecimiento'] ?? ''
    ];

    // ================================================
    // CAPTURAR DATOS DEL CLIENTE
    // ================================================
    $datos['cliente'] = [
        'nombre_cliente'              => $_POST['nombre_cliente'],
        'documento_cliente'           => $_POST['documento_cliente'],
        'direccion_cliente'           => $_POST['direccion_cliente'],
        'telefono_cliente'            => $_POST['telefono_cliente'],
        'correo_cliente'              => $_POST['correo_cliente'],
        'nombre_comercial_cliente'    => $_POST['nombre_comercial_cliente'] ?? '',
        'nrc_cliente'                 => $tipo_documento === "03" ? ($_POST['nrc_cliente'] ?? "") : "",
        'giro_cliente'                => $tipo_documento === "03" ? ($_POST['giro_cliente'] ?? "") : "",
        'actividad_cliente'           => $tipo_documento === "03" ? ($_POST['actividad_cliente'] ?? "") : ""
    ];

    // ================================================
    // DETALLE DE ÍTEMS Y CÁLCULOS
    // ================================================
    $datos['items'] = [];

    $suma_no_sujeta = 0;
    $suma_exenta    = 0;
    $suma_gravada   = 0;

    if (!empty($_POST['items'])) {

        foreach ($_POST['items'] as $i => $item) {

            $cantidad       = floatval($item['cantidad'] ?? 0);
            $precio         = floatval($item['precio_unitario'] ?? 0);
            $categoria      = $item['categoria'] ?? "gravada";

            // Calcular venta según categoría (solo una categoría por ítem)
            $venta_no_sujeta = $categoria === "no_sujeta" ? $cantidad * $precio : 0;
            $venta_exenta    = $categoria === "exenta" ? $cantidad * $precio : 0;
            $venta_gravada   = $categoria === "gravada" ? $cantidad * $precio : 0;

            $datos['items'][] = [
                'numero'          => $i + 1,
                'cantidad'        => $cantidad,
                'codigo'          => $item['codigo'] ?? "",
                'descripcion'     => $item['descripcion'] ?? "",
                'precio_unitario' => $precio,
                'venta_no_sujeta' => $venta_no_sujeta,
                'venta_exenta'    => $venta_exenta,
                'venta_gravada'   => $venta_gravada,
                'categoria'       => $categoria
            ];

            $suma_no_sujeta += $venta_no_sujeta;
            $suma_exenta    += $venta_exenta;
            $suma_gravada   += $venta_gravada;
        }
    }

    // ================================================
    // CÁLCULOS FINALES
    // ================================================
    // IVA al 13% aplica solo a ventas gravadas
    $iva = $suma_gravada * 0.13;

    // IVA retenido al 1% aplica solo para CCF sobre ventas gravadas
    $iva_retenido = ($tipo_documento === "03") ? $suma_gravada * 0.01 : 0;

    // Total general = suma de todas las ventas + IVA - IVA retenido
    $total_general = $suma_no_sujeta + $suma_exenta + $suma_gravada + $iva - $iva_retenido;

    $datos['calculos'] = [
        'suma_no_sujeta' => $suma_no_sujeta,
        'suma_exenta'    => $suma_exenta,
        'suma_gravada'   => $suma_gravada,
        'iva'            => $iva,
        'iva_retenido'   => $iva_retenido,
        'total_general'  => $total_general
    ];

    // ================================================
    // OTROS DATOS PARA EL DOCUMENTO
    // ================================================
    $datos['numero_documento'] = $_POST['numero_documento'] ?? '00000001';
    $datos['fecha'] = date('d/m/Y');
    $datos['tipo_documento'] = $tipo_documento;

    // ================================================
    // GENERAR PDF SEGÚN TIPO DE DOCUMENTO
    // ================================================
    if ($tipo_documento === "01") {
        generarPDF($datos); // plantilla_factura.php
    } else {
        generarPDF_CCF($datos); // plantilla_ccf.php
    }
}
?>
