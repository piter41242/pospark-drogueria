<?php
// importar.php - Script para cargar inventario masivo
set_time_limit(0); // Evitar que se corte por tiempo
require_once("Modelo/conexion.php"); // Asegúrate de que la ruta sea correcta
$conexionBD = BD::crearInstancia();

echo "<h3>Iniciando importación de medicamentos...</h3>";

// 1. Abrimos el archivo CSV
$archivo = fopen("csv/medicamentos.csv", "r");
if (!$archivo) {
    die("Error: No se encontró el archivo csv/medicamentos.csv");
}

// 2. Creamos un Laboratorio genérico por si el CSV trae uno que no existe
$conexionBD->exec("INSERT IGNORE INTO laboratorio (nombre) VALUES ('Genérico'), ('Bayer'), ('Pfizer'), ('Tecnoquímicas'), ('MK'), ('Lafrancol')");

$fila = 0;
$insertados = 0;

// 3. Leemos línea por línea
while (($datos = fgetcsv($archivo, 10000, ",")) !== FALSE) {
    $fila++;
    if ($fila == 1) continue; // Saltamos la primera fila (los encabezados)

    // Mapeamos las columnas del CSV según lo que compartiste
    // 0: Expediente, 1: principio_activo, 2: concentracion, 3: unidad_base, 4: unidad_dispensacion, 
    // 5: nombre_comercial, 6: fabricante, 7: medicamento, 8: canal, 9: precio_por_tableta
    $principio_activo = trim($datos[1] ?? '');
    $concentracion    = trim($datos[2] ?? '');
    $nombre_comercial = trim($datos[5] ?? '');
    $fabricante       = trim($datos[6] ?? 'Genérico');
    
    // Limpiamos el precio (viene con formato "3.752,866667" o "329,295281")
    $precio_string = $datos[9] ?? '0';
    $precio_string = str_replace(".", "", $precio_string); // Quitamos puntos de miles
    $precio_string = str_replace(",", ".", $precio_string); // Cambiamos coma por punto
    $precio_venta = (float)$precio_string;
    
    // Si el precio es 0 o menor, le ponemos un valor por defecto
    if ($precio_venta <= 0) $precio_venta = 1000;

    // Evitar nombres vacíos o problemas con caracteres
    if (empty($nombre_comercial) || empty($principio_activo)) continue;

    // Verificamos si el laboratorio existe en la base de datos, si no, lo creamos
    $stmtLab = $conexionBD->prepare("SELECT idLaboratorio FROM laboratorio WHERE nombre = ? LIMIT 1");
    $stmtLab->execute([$fabricante]);
    $lab = $stmtLab->fetch();
    
    if ($lab) {
        $idLaboratorio = $lab['idLaboratorio'];
    } else {
        $stmtInsertLab = $conexionBD->prepare("INSERT INTO laboratorio (nombre) VALUES (?)");
        $stmtInsertLab->execute([$fabricante]);
        $idLaboratorio = $conexionBD->lastInsertId();
    }

    // Creamos un código interno único (Usamos las iniciales del nombre + número de fila)
    $codigo_interno = "MED-" . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nombre_comercial), 0, 3)) . "-" . $fila;

    // Insertamos el producto
    // Nota: Asignamos Categoría 1, Presentación 1 e Impuesto 1 por defecto
    $sqlProd = $conexionBD->prepare("
        INSERT INTO producto (codigo_barras, codigo_interno, nombre, principio_activo, concentracion, idPresentacion, idLaboratorio, idCategoria, idImpuesto, valorCompra, valorVenta, stock_minimo, estado) 
        VALUES (NULL, ?, ?, ?, ?, 1, ?, 1, 1, ?, ?, 5, 1)
    ");
    
    $insertOK = $sqlProd->execute([
        $codigo_interno, 
        $nombre_comercial, 
        $principio_activo, 
        $concentracion, 
        $idLaboratorio, 
        ($precio_venta * 0.7), // Costo (70% del precio de venta)
        $precio_venta
    ]);

    if ($insertOK) {
        $idProducto = $conexionBD->lastInsertId();
        $insertados++;

        // Como el producto no tiene stock, le creamos un Lote falso inicial
        // para que aparezca en el inventario con 20 unidades
        $sqlLote = $conexionBD->prepare("
            INSERT INTO lote (idProducto, idSede, codigo_lote, fecha_entrada, fecha_vencimiento, cantidad_inicial, cantidad_actual, costo_unitario, estado) 
            VALUES (?, 1, 'LOTE-INICIAL', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 20, 20, ?, 1)
        ");
        $sqlLote->execute([$idProducto, ($precio_venta * 0.7)]);

        // Registrar en Kardex (Entrada)
        $sqlKardex = $conexionBD->prepare("
            INSERT INTO kardex (idProducto, idSede, tipo, cantidad, saldo, referencia, usuario, fecha) 
            VALUES (?, 1, 'ENTRADA', 20, 20, 'CARGA_MASIVA', 1, NOW())
        ");
        $sqlKardex->execute([$idProducto]);
    }
}

fclose($archivo);

echo "<h4>¡Importación completada!</h4>";
echo "<p>Se procesaron " . ($fila - 1) . " filas del CSV.</p>";
echo "<p>Se insertaron <b>$insertados</b> productos nuevos en el inventario con 20 unidades cada uno.</p>";
echo "<a href='index.php?ps=login'>Ir al sistema</a>";
?>