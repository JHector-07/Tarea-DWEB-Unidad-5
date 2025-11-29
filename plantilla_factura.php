<?php
// parte de Hector
require_once __DIR__ . '/dompdf/autoload.inc.php';

// Importación de clases Dompdf 
use Dompdf\Dompdf;
use Dompdf\Options;

function generarPDF($datos)
{
    ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura / CCF</title>

<style>
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        margin: 40px;
        color: #333;
        font-size: 12px;
    }

    h2 {
        text-align: center;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .subtitulo {
        text-align: center;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .section-title {
        background-color: #eee;
        padding: 6px;
        font-weight: bold;
        border: 1px solid #ccc;
        margin-top: 25px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        font-size: 12px;
    }

    table td, table th {
        border: 1px solid #666;
        padding: 6px;
    }

    table th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: center;
    }

    .totales td {
        font-weight: bold;
        background: #fafafa;
    }

    .right {
        text-align: right;
    }
</style>
</head>

<body>

<!-- TIPO DE DOCUMENTO -->
<h2>
    <?= $datos['tipo_documento'] === '01' ? 'FACTURA' : 'COMPROBANTE DE CRÉDITO FISCAL' ?>
</h2>

<div class="subtitulo">Documento N°: <?= $datos['numero_documento'] ?></div>
<div class="subtitulo">Fecha: <?= $datos['fecha'] ?></div>

<!-- DATOS DEL EMISOR -->
<div class="section-title">Datos del Emisor</div>

<table>
    <tr><td><strong>Razón Social:</strong></td><td><?= $datos['emisor']['nombre_emisor'] ?></td></tr>
    <tr><td><strong>NIT:</strong></td><td><?= $datos['emisor']['nit_emisor'] ?></td></tr>
    <tr><td><strong>NRC:</strong></td><td><?= $datos['emisor']['nrc_emisor'] ?></td></tr>
    <tr><td><strong>Actividad Económica:</strong></td><td><?= $datos['emisor']['actividad_economica'] ?></td></tr>
    <tr><td><strong>Dirección:</strong></td><td><?= $datos['emisor']['direccion_emisor'] ?></td></tr>
    <tr><td><strong>Teléfono:</strong></td><td><?= $datos['emisor']['telefono_emisor'] ?></td></tr>
    <tr><td><strong>Correo:</strong></td><td><?= $datos['emisor']['correo_emisor'] ?></td></tr>
</table>

<!-- DATOS DEL CLIENTE -->
<div class="section-title">Datos del Cliente</div>

<table>
    <tr><td><strong>Nombre / Razón Social:</strong></td><td><?= $datos['cliente']['nombre_cliente'] ?></td></tr>
    <tr><td><strong>Documento:</strong></td><td><?= $datos['cliente']['documento_cliente'] ?></td></tr>
    <tr><td><strong>Dirección:</strong></td><td><?= $datos['cliente']['direccion_cliente'] ?></td></tr>
    <tr><td><strong>Teléfono:</strong></td><td><?= $datos['cliente']['telefono_cliente'] ?></td></tr>
    <tr><td><strong>Correo:</strong></td><td><?= $datos['cliente']['correo_cliente'] ?></td></tr>
</table>

<!-- DETALLE -->
<div class="section-title">Detalle de Ítems</div>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Cantidad</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>No Sujeta</th>
            <th>Exenta</th>
            <th>Gravada</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($datos['items'] as $item): ?>
            <tr>
                <td><?= $item['numero'] ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td><?= $item['codigo'] ?></td>
                <td><?= $item['descripcion'] ?></td>
                <td>$<?= number_format($item['venta_no_sujeta'], 2) ?></td>
                <td>$<?= number_format($item['venta_exenta'], 2) ?></td>
                <td>$<?= number_format($item['venta_gravada'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- TOTALES -->
<div class="section-title">Totales</div>

<table class="totales">
    <tr><td>Total No Sujetas:</td><td class="right">$<?= number_format($datos['calculos']['suma_no_sujeta'], 2) ?></td></tr>
    <tr><td>Total Exentas:</td><td class="right">$<?= number_format($datos['calculos']['suma_exenta'], 2) ?></td></tr>
    <tr><td>Total Gravadas:</td><td class="right">$<?= number_format($datos['calculos']['suma_gravada'], 2) ?></td></tr>
    <tr><td>IVA (13%):</td><td class="right">$<?= number_format($datos['calculos']['iva'], 2) ?></td></tr>
    <tr><td>IVA Retenido:</td><td class="right">$<?= number_format($datos['calculos']['iva_retenido'], 2) ?></td></tr>
    <tr><td><strong>Total General:</strong></td><td class="right"><strong>$<?= number_format($datos['calculos']['total_general'], 2) ?></strong></td></tr>
</table>

</body>
</html>

<?php
   
    $html = ob_get_clean();
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("factura.pdf", ["Attachment" => false]);
    
}

?>
