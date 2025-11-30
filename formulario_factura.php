<?php 
/**
 * formulario_factura.php
 * 
 * Formulario principal para emitir Facturas (01) o Comprobantes de Crédito Fiscal (03).
 * Incluye validación de campos, manejo dinámico de ítems y visualización de errores.
 */

session_start();

// Recuperar errores y datos previos
$errores = $_SESSION['errores'] ?? [];
$datos = $_SESSION['datos'] ?? [];
unset($_SESSION['errores'], $_SESSION['datos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura / CCF</title>
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

<script>
/**
 * Muestra/oculta los campos adicionales para CCF según tipo de documento seleccionado
 */
function toggleDatosCCF() {
    let tipo = document.querySelector("select[name='tipo_documento']").value;
    let bloque = document.getElementById("datos_ccf");

    if (tipo === "03") {
        bloque.style.display = "block";
    } else {
        bloque.style.display = "none";

        // Limpiar campos si se cambia a factura
        document.getElementById("nrc_cliente").value = "";
        document.getElementById("giro_cliente").value = "";
        document.getElementById("actividad_cliente").value = "";
    }
}

/**
 * Valida que un campo tenga solo números
 */
function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9.-]/g, '');
}

/**
 * Agrega una nueva fila de ítem a la tabla dinámicamente
 */
function agregarItem() {
    let tabla = document.getElementById("tablaItems").querySelector("tbody");
    let filaCount = tabla.rows.length + 1;

    let fila = `
        <tr>
            <td>${filaCount}</td>
            <td><input type="number" name="items[${filaCount}][cantidad]" step="0.01" min="0" oninput="soloNumeros(this)"></td>
            <td><input type="text" name="items[${filaCount}][codigo]"></td>
            <td><input type="text" name="items[${filaCount}][descripcion]"></td>
            <td><input type="number" step="0.01" name="items[${filaCount}][precio_unitario]" min="0" oninput="soloNumeros(this)"></td>
            <td>
                <select name="items[${filaCount}][categoria]">
                    <option value="no_sujeta">No Sujeta</option>
                    <option value="exenta">Exenta</option>
                    <option value="gravada" selected>Gravada</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-eliminar" onclick="eliminarFila(this)">X</button></td>
        </tr>`;
    tabla.insertAdjacentHTML('beforeend', fila);
}

/**
 * Elimina una fila de ítem de la tabla
 */
function eliminarFila(btn) {
    btn.parentNode.parentNode.remove();
}
</script>
</head>

<body onload="toggleDatosCCF()">

<div class="container">

<form action="procesar.php" method="POST">

    <h2>Factura / Comprobante de Crédito Fiscal</h2>

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


    <!-- TIPO DE DOCUMENTO -->
    <h3>Tipo de Documento</h3>
    <select name="tipo_documento" required onchange="toggleDatosCCF()">
        <option value="01" <?= ($datos['tipo_documento'] ?? '')=='01' ? 'selected' : '' ?>>01 - Factura</option>
        <option value="03" <?= ($datos['tipo_documento'] ?? '')=='03' ? 'selected' : '' ?>>03 - Comprobante de Crédito Fiscal</option>
    </select>


    <!-- DATOS DEL EMISOR -->
    <h3>Datos del Emisor</h3>
    <?php
    $campos_emisor = [
        'nombre_emisor'=>'Nombre / Razón Social',
        'nit_emisor'=>'NIT',
        'nrc_emisor'=>'NRC',
        'actividad_economica'=>'Actividad Económica',
        'direccion_emisor'=>'Dirección',
        'telefono_emisor'=>'Teléfono',
        'correo_emisor'=>'Correo',
        'nombre_comercial'=>'Nombre Comercial',
        'establecimiento'=>'Establecimiento'
    ];

    foreach ($campos_emisor as $campo=>$label):
        $valor = $datos[$campo] ?? '';
    ?>
        <label><?= $label ?></label>
        <input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>">
    <?php endforeach; ?>


    <!-- DATOS DEL CLIENTE -->
    <h3>Datos del Cliente</h3>
    <?php
    $campos_cliente = [
        'nombre_cliente'=>'Nombre / Razón Social',
        'documento_cliente'=>'Documento (NIT o DUI)',
        'direccion_cliente'=>'Dirección',
        'telefono_cliente'=>'Teléfono',
        'correo_cliente'=>'Correo',
        'nombre_comercial_cliente'=>'Nombre Comercial'
    ];

    foreach ($campos_cliente as $campo=>$label):
        $valor = $datos[$campo] ?? '';
    ?>
        <label><?= $label ?></label>
        <input type="text" name="<?= $campo ?>" value="<?= htmlspecialchars($valor) ?>">
    <?php endforeach; ?>


    <!-- 🔵 CAMPOS ESPECIALES PARA CCF -->
    <div id="datos_ccf" style="display:none;">
        <h3>Datos adicionales para CCF</h3>

        <label>NRC del Cliente</label>
        <input type="text" id="nrc_cliente" name="nrc_cliente" value="<?= $datos['nrc_cliente'] ?? '' ?>">

        <label>Giro del Cliente</label>
        <input type="text" id="giro_cliente" name="giro_cliente" value="<?= $datos['giro_cliente'] ?? '' ?>">

        <label>Actividad Económica</label>
        <input type="text" id="actividad_cliente" name="actividad_cliente" value="<?= $datos['actividad_cliente'] ?? '' ?>">
    </div>


    <!-- ITEMS -->
    <h3>Ítems</h3>

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
                <td><input type="number" name="items[<?= $contador ?>][cantidad]" step="0.01" min="0" value="<?= $item['cantidad'] ?>" oninput="soloNumeros(this)"></td>
                <td><input type="text" name="items[<?= $contador ?>][codigo]" value="<?= $item['codigo'] ?>"></td>
                <td><input type="text" name="items[<?= $contador ?>][descripcion]" value="<?= $item['descripcion'] ?>"></td>
                <td><input type="number" name="items[<?= $contador ?>][precio_unitario]" step="0.01" min="0" value="<?= $item['precio_unitario'] ?>" oninput="soloNumeros(this)"></td>
                <td>
                    <select name="items[<?= $contador ?>][categoria]">
                        <option value="no_sujeta" <?= $item['categoria']=='no_sujeta'?'selected':'' ?>>No Sujeta</option>
                        <option value="exenta" <?= $item['categoria']=='exenta'?'selected':'' ?>>Exenta</option>
                        <option value="gravada" <?= $item['categoria']=='gravada'?'selected':'' ?>>Gravada</option>
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

    <button type="button" class="btn" onclick="agregarItem()">Agregar Ítem</button>

    <br><br>
    <button type="submit" class="btn">Generar PDF</button>

</form>
</div>

</body>
</html>
