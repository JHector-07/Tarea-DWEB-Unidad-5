<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

function generarPDF($datos)
{
    ob_start();

    $numero_documento = $datos['numero_documento'] ?? '00000001';
    $fecha = $datos['fecha'] ?? date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura</title>

<style>
    body {
        font-family: DejaVu Sans, Arial;
        font-size: 11px;
        margin: 20px;
        color: #333;
    }

    .container {
        width: 95%;
        margin: 0 auto;
    }

    /* ENCABEZADO */
    .header {
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 2px solid #2c3e50;
        margin-bottom: 20px;
    }
    .header h1 {
        font-size: 26px;
        color: #2c3e50;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .subtitulo {
        font-size: 12px;
        color: #555;
        margin-top: 2px;
    }

    /* TITULOS */
    .section-title {
        margin-top: 18px;
        background: #2c3e50;
        color: #fff;
        padding: 7px;
        font-weight: bold;
        font-size: 12px;
        text-align: center;
        border-radius: 3px;
    }

    /* TABLAS --------------------- */
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
        width: 28%;
        font-weight: bold;
    }

    /* ITEMS */
    .items-table th {
        background: #34495e;
        color: #fff;
        padding: 8px;
        border: 1px solid #34495e;
        font-size: 11px;
    }
    .items-table td {
        padding: 7px;
        border: 1px solid #ccc;
        font-size: 11px;
    }
    .descripcion { text-align: left; }

    .cantidad, .precio, .total {
        text-align: right;
    }

    /* TOTALES ---------------------------- */
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
        padding: 10px;
        border: 1px solid #bbb;
    }

    .label {
        background: #f4f4f4;
        font-weight: bold;
        text-align: right;
    }

    .total-general {
        background: #27ae60;
        color: #fff;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>FACTURA</h1>
        <div class="subtitulo">Documento N°: <?= $numero_documento ?></div>
        <div class="subtitulo">Fecha: <?= $fecha ?></div>
    </div>

    <!-- EMISOR -->
    <div class="section-title">Datos del Emisor</div>
    <table class="data-table">
        <?php foreach ($datos['emisor'] as $key => $value): ?>
        <tr>
            <td><?= ucfirst(str_replace("_", " ", $key)) ?>:</td>
            <td><?= htmlspecialchars($value) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- CLIENTE -->
    <div class="section-title">Datos del Cliente</div>
    <table class="data-table">
        <?php 
        // Definir campos según tipo de documento
        $tipo_doc = $datos['tipo_documento'] ?? '01';
        
        if ($tipo_doc === '01') {
            // FACTURA - Solo campos básicos
            $campos_mostrar = ['nombre_cliente', 'documento_cliente', 'direccion_cliente', 'telefono_cliente', 'correo_cliente'];
        } else {
            // CCF - Todos los campos
            $campos_mostrar = array_keys($datos['cliente']);
        }
        
        foreach ($campos_mostrar as $key):
            if (isset($datos['cliente'][$key])):
        ?>
        <tr>
            <td><?= ucfirst(str_replace("_", " ", $key)) ?>:</td>
            <td><?= htmlspecialchars($datos['cliente'][$key]) ?></td>
        </tr>
        <?php 
            endif;
        endforeach; 
        ?>
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
            <td class="cantidad"><?= number_format($i['cantidad'], 2) ?></td>
            <td><?= $i['codigo'] ?></td>
            <td class="descripcion"><?= $i['descripcion'] ?></td>
            <td class="precio">$<?= number_format($i['precio_unitario'], 2) ?></td>
            <td class="total">$<?= number_format($i['venta_no_sujeta'], 2) ?></td>
            <td class="total">$<?= number_format($i['venta_exenta'], 2) ?></td>
            <td class="total">$<?= number_format($i['venta_gravada'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- TOTALES -->
    <div class="section-title">Resumen de Totales</div>
    <div class="totales-container">
        <table class="totales">
            <?php foreach ($datos['calculos'] as $key => $value): ?>
            <tr>
                <td class="label"><?= ucfirst(str_replace("_", " ", $key)) ?>:</td>
                <td class="<?= $key == 'total_general' ? 'total-general' : '' ?>">
                    $<?= number_format($value, 2) ?>
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
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("factura.pdf", ["Attachment" => false]);
}
?>
