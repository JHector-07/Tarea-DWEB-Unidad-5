<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

function generarPDF_CCF($datos)
{
    ob_start();

    $numero_documento = $datos['numero_documento'] ?? '00000001';
    $fecha = $datos['fecha'] ?? date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>CCF</title>

<style>
    body {
        font-family: DejaVu Sans, Arial;
        font-size: 11px;
        margin: 20px;
    }

    .container {
        width: 95%;
        margin: 0 auto;
    }

    /* ENCABEZADO */
    .header {
        text-align: center;
        border-bottom: 2px solid #c0392b;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .header h1 {
        color: #c0392b;
        font-size: 26px;
        font-weight: bold;
    }

    /* SECCIONES */
    .section-title {
        margin-top: 18px;
        background: #c0392b;
        color: white;
        padding: 8px;
        font-size: 12px;
        text-align: center;
        border-radius: 3px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .data-table td {
        padding: 7px;
        border: 1px solid #ccc;
    }
    .data-table td:first-child {
        background: #f4f4f4;
        font-weight: bold;
        width: 28%;
    }

    /* ITEMS */
    .items-table th {
        background: #34495e;
        color: #fff;
        padding: 8px;
        border: 1px solid #34495e;
    }
    .items-table td {
        padding: 7px;
        border: 1px solid #ccc;
    }

    /* TOTALES */
    .totales-container {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .totales {
        width: 60%;
        border-collapse: collapse;
    }

    .totales td {
        border: 1px solid #bbb;
        padding: 10px;
    }

    .label {
        background: #f4f4f4;
        text-align: right;
        font-weight: bold;
    }

    .total-general {
        background: #c0392b;
        color: #fff;
        font-weight: bold;
    }
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>COMPROBANTE DE CRÉDITO FISCAL</h1>
        <div>N° <?= $numero_documento ?> — Fecha: <?= $fecha ?></div>
    </div>

    <!-- EMISOR -->
    <div class="section-title">Datos del Emisor</div>
    <table class="data-table">
        <?php foreach ($datos['emisor'] as $k => $v): ?>
        <tr>
            <td><?= ucfirst(str_replace("_"," ",$k)) ?>:</td>
            <td><?= htmlspecialchars($v) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- CLIENTE -->
    <div class="section-title">Datos del Cliente</div>
    <table class="data-table">
        <?php foreach ($datos['cliente'] as $k => $v): ?>
        <tr>
            <td><?= ucfirst(str_replace("_"," ",$k)) ?>:</td>
            <td><?= htmlspecialchars($v) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- ITEMS -->
    <div class="section-title">Detalle de Ítems</div>
    <table class="items-table">
        <tr>
            <th>#</th><th>Cant</th><th>Código</th>
            <th>Descripción</th><th>Precio</th>
            <th>No Suj</th><th>Exenta</th><th>Gravada</th>
        </tr>

        <?php foreach ($datos['items'] as $i): ?>
        <tr>
            <td><?= $i['numero'] ?></td>
            <td><?= number_format($i['cantidad'], 2) ?></td>
            <td><?= $i['codigo'] ?></td>
            <td><?= $i['descripcion'] ?></td>
            <td>$<?= number_format($i['precio_unitario'], 2) ?></td>
            <td>$<?= number_format($i['venta_no_sujeta'], 2) ?></td>
            <td>$<?= number_format($i['venta_exenta'], 2) ?></td>
            <td>$<?= number_format($i['venta_gravada'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- TOTALES -->
    <div class="section-title">Resumen de Totales</div>

    <div class="totales-container">
        <table class="totales">
            <?php foreach ($datos['calculos'] as $k => $v): ?>
            <tr>
                <td class="label"><?= ucfirst(str_replace("_"," ",$k)) ?>:</td>
                <td class="<?= $k == 'total_general' ? 'total-general': '' ?>">
                    $<?= number_format($v, 2) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

</body>
</html>

<?php
    $html = ob_get_clean();
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4','portrait');
    $dompdf->render();
    $dompdf->stream("ccf.pdf", ["Attachment"=>false]);
}
?>
