<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<!-- parte de Eduardo y Leslie -->

    <h1>Simulación: Enviar datos con POST</h1>
        <form method="POST" action="procesar.php"> 
            
            <!-- Este formulario es para probar la recepcion de datos en php, cambiarlo cuando hagan su parte -->

            <label for="tipo_documento">Tipo de Documento:</label>
            <select name="tipo_documento" id="tipo_documento" required>
                <option value="01">01-Factura (Consumidor Final)</option>
                <option value="03">03-Comprobante de Crédito Fiscal</option>
            </select><br><br>
            
            <label for="nombre_emisor">Nombre / Razón Social (Emisor):</label>
            <input type="text" name="nombre_emisor" id="nombre_emisor" value="Mi Empresa S.A. de C.V." required><br><br>
            
            <label for="cantidad_item_1">Cantidad del Ítem:</label>
            <input type="number" name="items[0][cantidad]" id="cantidad_item_1" value="2" required><br>
            
            <label for="descripcion_item_1">Descripción del Ítem:</label>
            <input type="text" name="items[0][descripcion]" id="descripcion_item_1" value="Servicio de Consultoría" required><br>
            
            <label for="precio_item_1">Precio Unitario:</label>
            <input type="number" step="0.01" name="items[0][precio_unitario]" id="precio_item_1" value="50.00" required><br><br>

            <button type="submit">Generar PDF (POST)</button>
        </form>
</body>
</html>


