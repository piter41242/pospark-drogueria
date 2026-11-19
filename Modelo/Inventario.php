<?php
class Inventario
{
    public function consultarPaginado($idSede, $busqueda, $inicio, $limite)
    {
        $conexionBD = BD::crearInstancia();

        $where = "1=1";
        $params = [];

        if (!empty($busqueda)) {
            $where .= " AND (p.nombre LIKE ? OR p.codigo_interno LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        // Hacemos la consulta directa, sumando los lotes
        $sql = $conexionBD->prepare("
            SELECT 
                p.idProducto, 
                p.codigo_interno, 
                p.nombre, 
                p.stock_minimo,
                COALESCE(SUM(l.cantidad_actual - l.cantidad_reservada), 0) AS stock_disponible,
                CASE WHEN COALESCE(SUM(l.cantidad_actual - l.cantidad_reservada), 0) <= p.stock_minimo THEN 1 ELSE 0 END AS alerta
            FROM producto p
            LEFT JOIN lote l ON l.idProducto = p.idProducto AND l.idSede = ? AND l.estado = 1
            WHERE $where
            GROUP BY p.idProducto
            ORDER BY alerta DESC, p.nombre ASC
            LIMIT $inicio, $limite
        ");

        // El primer parámetro es el idSede para el JOIN
        array_unshift($params, $idSede);

        $sql->execute($params);
        return $sql->fetchAll();
    }

    public function contarTotal($idSede, $busqueda)
    {
        $conexionBD = BD::crearInstancia();
        $where = "1=1";
        $params = [];

        if (!empty($busqueda)) {
            $where .= " AND (nombre LIKE ? OR codigo_interno LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        // Contamos todos los productos que pertenecen a la sede (tengan o no lotes)
        $sql = $conexionBD->prepare("SELECT COUNT(*) as total FROM producto WHERE $where");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'];
    }

    // Obtener un producto específico para el ajuste
    public function buscarProducto($idProducto, $idSede)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM v_stock WHERE idProducto = ? AND idSede = ?");
        $sql->execute(array($idProducto, $idSede));
        return $sql->fetch();
    }
    // Traer todos los productos para el select del formulario
    public function consultarTodosProductos()
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->query("SELECT idProducto, nombre, codigo_interno FROM producto WHERE estado = 1 ORDER BY nombre ASC");
        return $sql->fetchAll();
    }

    // Crear un producto nuevo
    public function crearProducto($codigo_interno, $nombre, $precio_compra, $precio_venta, $idCategoria, $idPresentacion, $idLaboratorio, $idImpuesto, $stock_minimo)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            INSERT INTO producto (codigo_interno, nombre, valorCompra, valorVenta, idCategoria, idPresentacion, idLaboratorio, idImpuesto, stock_minimo, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        return $sql->execute(array($codigo_interno, $nombre, $precio_compra, $precio_venta, $idCategoria, $idPresentacion, $idLaboratorio, $idImpuesto, $stock_minimo));
    }

    // Registrar un lote (Ingreso de mercancía)
    public function ingresarLote($idProducto, $idSede, $codigo_lote, $fecha_vencimiento, $cantidad, $costo_unitario)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            INSERT INTO lote (idProducto, idSede, codigo_lote, fecha_entrada, fecha_vencimiento, cantidad_inicial, cantidad_actual, costo_unitario, estado) 
            VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, 1)
        ");
        $sql->execute(array($idProducto, $idSede, $codigo_lote, $fecha_vencimiento, $cantidad, $cantidad, $costo_unitario));

        $idLote = $conexionBD->lastInsertId();

        // Registrar en Kardex
        $sqlKardex = $conexionBD->prepare("
            INSERT INTO kardex (idProducto, idLote, idSede, tipo, cantidad, saldo, referencia, usuario, fecha, observacion) 
            VALUES (?, ?, ?, 'ENTRADA', ?, ?, 'INGRESO_LOTE', ?, NOW(), 'Ingreso por compra/creación')
        ");
        $sqlKardex->execute(array($idProducto, $idLote, $idSede, $cantidad, $cantidad, $_SESSION['usuario']['idUsuario']));

        return true;
    }
    // Ajustar stock (Crear un lote de ajuste o actualizar el kardex)
    public function ajustarStock($idProducto, $idSede, $cantidad, $tipo, $motivo, $idUsuario)
    {
        $conexionBD = BD::crearInstancia();

        // 1. Buscamos si hay un lote con stock disponible para este producto en esta sede
        $sqlLote = $conexionBD->prepare("
            SELECT idLote, cantidad_actual FROM lote 
            WHERE idProducto = ? AND idSede = ? AND estado = 1 
            ORDER BY fecha_vencimiento ASC LIMIT 1
        ");
        $sqlLote->execute([$idProducto, $idSede]);
        $lote = $sqlLote->fetch();

        if ($lote) {
            // Si hay lote, actualizamos su cantidad
            $nuevaCantidad = $tipo === 'ENTRADA' ? ($lote['cantidad_actual'] + $cantidad) : ($lote['cantidad_actual'] - $cantidad);

            // Evitar stock negativo
            if ($nuevaCantidad < 0) $nuevaCantidad = 0;

            $sqlUpdate = $conexionBD->prepare("UPDATE lote SET cantidad_actual = ? WHERE idLote = ?");
            $sqlUpdate->execute([$nuevaCantidad, $lote['idLote']]);
            $idLote = $lote['idLote'];
            $saldo = $nuevaCantidad;
        } else {
            // Si no hay lote (ej. producto nuevo que nunca ha tenido stock), creamos uno de ajuste
            $sqlInsertLote = $conexionBD->prepare("
                INSERT INTO lote (idProducto, idSede, codigo_lote, fecha_entrada, fecha_vencimiento, cantidad_inicial, cantidad_actual, costo_unitario, estado) 
                VALUES (?, ?, 'AJUSTE', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?, ?, 0, 1)
            ");
            $sqlInsertLote->execute([$idProducto, $idSede, $cantidad, $cantidad]);
            $idLote = $conexionBD->lastInsertId();
            $saldo = $cantidad;
        }

        // 2. Registrar en el Kardex
        $sqlKardex = $conexionBD->prepare("
            INSERT INTO kardex (idProducto, idLote, idSede, tipo, cantidad, saldo, referencia, usuario, fecha, observacion) 
            VALUES (?, ?, ?, ?, ?, ?, 'AJUSTE_MANUAL', ?, NOW(), ?)
        ");

        return $sqlKardex->execute([$idProducto, $idLote, $idSede, $tipo, $cantidad, $saldo, $idUsuario, $motivo]);
    }
    public function buscarProductosAjax($termino)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT idProducto, nombre, codigo_interno 
            FROM producto 
            WHERE (nombre LIKE ? OR codigo_interno LIKE ?) AND estado = 1
            LIMIT 15
        ");
        $termino = "%" . $termino . "%";
        $sql->execute([$termino, $termino]);
        return $sql->fetchAll();
    }
    public function consultarLotesProducto($idProducto, $idSede)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT idLote, codigo_lote, fecha_vencimiento, cantidad_actual, cantidad_reservada, costo_unitario 
            FROM lote 
            WHERE idProducto = ? AND idSede = ? AND estado = 1 AND cantidad_actual > 0
            ORDER BY fecha_vencimiento ASC
        ");
        $sql->execute([$idProducto, $idSede]);
        return $sql->fetchAll();
    }
}
