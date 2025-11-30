<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Facturas y CCF</title>
</head>
<body>

<!-- 
    NOTA: Este formulario de prueba es un ejemplo básico.
    Usar formulario_factura.php para acceder al formulario principal completo
    con validación, manejo de errores y diseño mejorado.
-->

    <h1>Generador de Facturas y CCF - PRUEBA RÁPIDA</h1>
    <p>Selecciona tipo de documento y completa los datos mínimos para generar un PDF</p>
    
    <form method="POST" action="procesar.php">
        
        <h3>Tipo de Documento</h3>
        <label>
            <input type="radio" name="tipo_documento" value="01" checked> 
            01 - Factura (Consumidor Final)
        </label>
        <label>
            <input type="radio" name="tipo_documento" value="03"> 
            03 - Comprobante de Crédito Fiscal
        </label>
        <br><br>

        <h3>Datos del Emisor</h3>
        <label>Nombre/Razón Social:</label>
        <input type="text" name="nombre_emisor" value="Mi Empresa S.A. de C.V." required><br>
        
        <label>NIT:</label>
        <input type="text" name="nit_emisor" value="1234-567890-123-4" required><br>
        
        <label>NRC:</label>
        <input type="text" name="nrc_emisor" value="123456" required><br>
        
        <label>Actividad Económica:</label>
        <input type="text" name="actividad_economica" value="Venta de Servicios" required><br>
        
        <label>Dirección:</label>
        <input type="text" name="direccion_emisor" value="Avenida Principal, San Salvador" required><br>
        
        <label>Teléfono:</label>
        <input type="text" name="telefono_emisor" value="2234-5678" required><br>
        
        <label>Correo:</label>
        <input type="email" name="correo_emisor" value="info@empresa.com" required><br>
        
        <label>Nombre Comercial (opcional):</label>
        <input type="text" name="nombre_comercial" value="Mi Empresa"><br>
        
        <label>Establecimiento (opcional):</label>
        <input type="text" name="establecimiento" value="Sucursal 01"><br><br>

        <h3>Datos del Cliente</h3>
        <label>Nombre/Razón Social:</label>
        <input type="text" name="nombre_cliente" value="Clientes Varios S.A." required><br>
        
        <label>Documento (NIT - formato: ####-######-###-#):</label>
        <input type="text" name="documento_cliente" value="5678-901234-567-8" required><br>
        
        <label>Dirección:</label>
        <input type="text" name="direccion_cliente" value="Calle Secundaria, San Salvador" required><br>
        
        <label>Teléfono:</label>
        <input type="text" name="telefono_cliente" value="2345-6789" required><br>
        
        <label>Correo:</label>
        <input type="email" name="correo_cliente" value="cliente@empresa.com" required><br>
        
        <label>Nombre Comercial (opcional):</label>
        <input type="text" name="nombre_comercial_cliente" value="Cliente"><br>
        
        <label>NRC Cliente (requerido solo para CCF):</label>
        <input type="text" name="nrc_cliente" value="654321"><br><br>

        <h3>Ítems</h3>
        <label>Cantidad del Ítem:</label>
        <input type="number" name="items[1][cantidad]" value="2" step="0.01" required><br>
        
        <label>Código:</label>
        <input type="text" name="items[1][codigo]" value="COD001"><br>
        
        <label>Descripción del Ítem:</label>
        <input type="text" name="items[1][descripcion]" value="Servicio de Consultoría" required><br>
        
        <label>Precio Unitario:</label>
        <input type="number" name="items[1][precio_unitario]" value="50.00" step="0.01" required><br>
        
        <label>Categoría:</label>
        <select name="items[1][categoria]">
            <option value="no_sujeta">No Sujeta</option>
            <option value="exenta">Exenta</option>
            <option value="gravada" selected>Gravada</option>
        </select><br><br>

        <button type="submit">Generar PDF</button>
        <p style="margin-top: 20px; color: #666; font-size: 12px;">
            Para acceder al formulario completo con mejor interfaz y validación integrada, 
            <a href="formulario_factura.php">haz clic aquí</a>
        </p>
    </form>
</body>
</html>


