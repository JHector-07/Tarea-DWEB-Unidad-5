<?php
session_start();

// Recuperar errores y datos previos de la sesión
$errores = $_SESSION['errores'] ?? [];
$datos = $_SESSION['datos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura de Crédito Fiscal</title>
<style>
    body { background:#1a1a1a; color:white; font-family:Arial; margin:0; padding:20px; }
    .container { width:95%; max-width:1100px; margin:auto; background:#252525; padding:20px; border-radius:10px; }
    h2 { text-align:center; margin-bottom:25px; }
    h3 { margin-top:25px; margin-bottom:10px; border-bottom:1px solid #555; padding-bottom:5px; }
    label { font-size:14px; }
    input, select { width:100%; padding:7px; margin:5px 0 15px 0; background:#333; border:1px solid #555; color:white; border-radius:5px; }
    input.error, select.error { border-color: #ff4d4d; background: #441111; }
    table { width:100%; border-collapse:collapse; margin-top:15px; }
    th, td { border:1px solid #555; padding:8px; text-align:center; }
    th { background:#333; }
    .btn { background:#0066ff; border:none; color:white; padding:8px 15px; border-radius:5px; cursor:pointer; margin-top:10px; }
    .btn:hover { background:#000bac; }
    .btn-eliminar { background:#cc0000; padding:5px 10px; }
    .error-list { background:#ff4d4d; color:white; padding:10px; border-radius:5px; margin-bottom:15px; }
</style>
</head>
<body>

<div class="container">

<form action="procesar.php" method="POST">

    <h2>Factura de Crédito Fiscal</h2>

    <?php if (!empty($errores)): ?>
        <div class="error-list">
            <strong>Errores de Validación:</strong>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h3>Tipo de Documento</h3>
    <select name="tipo_documento" required <?= isset($errores['tipo_documento']) ? 'class="error"' : '' ?>>
        <option value="01" <?= (isset($datos['tipo_documento']) && $datos['tipo_documento']=='01') ? 'selected' : '' ?>>01 - Factura</option>
        <option value="03" <?= (isset($datos['tipo_documento']) && $datos['tipo_documento']=='03') ? 'selected' : '' ?>>03 - Comprobante de Crédito Fiscal</option>
    </select>

    <h3>Datos del Emisor</h3>
    <?php
    $campos_emisor = [
        'nombre_emisor' => 'Nombre / Razón Social',
        'nit_emisor' => 'NIT',
        'nrc_emisor' => 'NRC',
        'actividad_economica' => 'Actividad Económica',
        'direccion_emisor' => 'Dirección',
        'telefono_emisor' => 'Teléfono',
        'correo_emisor' => 'Correo',
        'nombre_comercial' => 'Nombre Comercial',
        'establecimiento' => 'Establecimiento'
    ];
    foreach ($campos_emisor as $campo => $label):
        $valor = $datos[$campo] ?? '';
        $clase_error = in_array($campo, $errores) ? 'error' : '';
    ?>
        <label><?= $label ?></label>
        <input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>" class="<?= $clase_error ?>">
    <?php endforeach; ?>

    <h3>Datos del Cliente</h3>
    <?php
    $campos_cliente = [
        'nombre_cliente' => 'Nombre / Razón Social',
        'documento_cliente' => 'Documento (NIT o DUI)',
        'direccion_cliente' => 'Dirección',
        'telefono_cliente' => 'Teléfono',
        'correo_cliente' => 'Correo',
        'nombre_comercial_cliente' => 'Nombre Comercial'
    ];
    foreach ($campos_cliente as $campo => $label):
        $valor = $datos[$campo] ?? '';
        $clase_error = in_array($campo, $errores) ? 'error' : '';
    ?>
        <label><?= $label ?></label>
        <input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>" class="<?= $clase_error ?>">
    <?php endforeach; ?>

    <h3>Ítems de la Factura</h3>
    <table id="tablaItems">
        <thead>
            <tr>
                <th>N°</th>
                <th>Cantidad</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Precio Unitario</th>
                <th>Categoría</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $contador = 1;
            if (!empty($datos['items'])):
                foreach ($datos['items'] as $item):
            ?>
            <tr>
                <td><?= $contador ?></td>
                <td><input type="number" name="items[<?= $contador ?>][cantidad]" value="<?= htmlspecialchars($item['cantidad']) ?>"></td>
                <td><input type="text" name="items[<?= $contador ?>][codigo]" value="<?= htmlspecialchars($item['codigo']) ?>"></td>
                <td><input type="text" name="items[<?= $contador ?>][descripcion]" value="<?= htmlspecialchars($item['descripcion']) ?>"></td>
                <td><input type="number" name="items[<?= $contador ?>][precio_unitario]" step="0.01" value="<?= htmlspecialchars($item['precio_unitario']) ?>"></td>
                <td>
                    <select name="items[<?= $contador ?>][categoria]">
                        <option value="no_sujeta" <?= ($item['categoria']=='no_sujeta')?'selected':'' ?>>No Sujeta</option>
                        <option value="exenta" <?= ($item['categoria']=='exenta')?'selected':'' ?>>Exenta</option>
                        <option value="gravada" <?= ($item['categoria']=='gravada')?'selected':'' ?>>Gravada</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-eliminar" onclick="eliminarFila(this)">X</button></td>
            </tr>
            <?php
                    $contador++;
                endforeach;
            endif;
            ?>
        </tbody>
    </table>

    <button type="button" class="btn" onclick="agregarFila()">Agregar Ítem</button>
    <button type="submit" class="btn" style="background:green;">Generar PDF</button>

</form>
</div>

<script>
let contador = <?= $contador ?>;

function agregarFila() {
    const tabla = document.querySelector("#tablaItems tbody");
    const fila = document.createElement("tr");

    fila.innerHTML = `
        <td>${contador}</td>
        <td><input type="number" name="items[${contador}][cantidad]" class="cantidad" min="1" value="1"></td>
        <td><input type="text" name="items[${contador}][codigo]" class="codigo"></td>
        <td><input type="text" name="items[${contador}][descripcion]" class="descripcion"></td>
        <td><input type="number" name="items[${contador}][precio_unitario]" class="precio" step="0.01" min="0" value="0"></td>
        <td>
            <select name="items[${contador}][categoria]">
                <option value="no_sujeta">No Sujeta</option>
                <option value="exenta">Exenta</option>
                <option value="gravada">Gravada</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-eliminar" onclick="eliminarFila(this)">X</button></td>
    `;
    tabla.appendChild(fila);
    contador++;
}

function eliminarFila(boton) {
    boton.closest('tr').remove();
}
</script>

</body>
</html>
