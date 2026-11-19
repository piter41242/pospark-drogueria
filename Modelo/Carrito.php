<?php
class Carrito {
    
    // 1. Abrir un carrito nuevo
    public function abrir($idSede, $idCliente, $idUsuario) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("INSERT INTO carrito (idSede, idCliente, usuario_apertura, estado) VALUES (?, ?, ?, 'ABIERTO')");
        $sql->execute(array($idSede, $idCliente, $idUsuario));
        return $conexionBD->lastInsertId();
    }

    // 2. Buscar el carrito abierto del usuario
    public function buscarCarritoAbierto($idUsuario, $idSede) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM carrito WHERE usuario_apertura = ? AND idSede = ? AND estado = 'ABIERTO' ORDER BY fecha_apertura DESC LIMIT 1");
        $sql->execute(array($idUsuario, $idSede));
        return $sql->fetch();
    }

    // 3. Agregar producto al carrito (Buscando el lote FIFO)
    public function agregarProducto($idCarrito, $idProducto, $cantidad, $idUsuario) {
        $conexionBD = BD::crearInstancia();
        
        // Primero verificamos si hay stock suficiente en la vista v_stock
        $sqlStock = $conexionBD->prepare("SELECT stock_disponible FROM v_stock WHERE idProducto = ? AND idSede = (SELECT idSede FROM carrito WHERE idCarrito = ?)");
        $sqlStock->execute([$idProducto, $idCarrito]);
        $stock = $sqlStock->fetch();
        
        if (!$stock || $stock['stock_disponible'] < $cantidad) {
            return false; // No hay stock suficiente
        }

        // Buscamos si el producto ya está en el carrito
        $sql = $conexionBD->prepare("SELECT * FROM detallecarrito WHERE idCarrito = ? AND idProducto = ?");
        $sql->execute(array($idCarrito, $idProducto));
        $existe = $sql->fetch();

        if ($existe) {
            $nuevaCantidad = $existe['cantidad'] + $cantidad;
            $sql = $conexionBD->prepare("UPDATE detallecarrito SET cantidad = ?, subtotal = cantidad * valor WHERE idDetalle = ?");
            $sql->execute(array($nuevaCantidad, $existe['idDetalle']));
        } else {
            // Si es nuevo, buscamos el LOTE MÁS PRÓXIMO A VENCER (FIFO) con stock disponible
            $sqlLote = $conexionBD->prepare("
                SELECT idLote, cantidad_actual, cantidad_reservada, valorVenta 
                FROM lote l
                JOIN producto p ON p.idProducto = l.idProducto
                WHERE l.idProducto = ? AND l.idSede = (SELECT idSede FROM carrito WHERE idCarrito = ?) 
                  AND l.estado = 1 AND (l.cantidad_actual - l.cantidad_reservada) >= ?
                ORDER BY l.fecha_vencimiento ASC LIMIT 1
            ");
            $sqlLote->execute([$idProducto, $idCarrito, $cantidad]);
            $lote = $sqlLote->fetch();
            
            if ($lote) {
                // Insertamos en el detalle con el ID del lote encontrado
                $subtotal = $cantidad * $lote['valorVenta'];
                $sql = $conexionBD->prepare("
                    INSERT INTO detallecarrito (idCarrito, idProducto, idLote, cantidad, valor, subtotal, agregado_por) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $sql->execute([$idCarrito, $idProducto, $lote['idLote'], $cantidad, $lote['valorVenta'], $subtotal, $idUsuario]);
                
                // RESERVAMOS EL STOCK en el lote para que nadie más lo tome
                $sqlReserva = $conexionBD->prepare("UPDATE lote SET cantidad_reservada = cantidad_reservada + ? WHERE idLote = ?");
                $sqlReserva->execute([$cantidad, $lote['idLote']]);
            } else {
                return false; // No hay ningún lote con stock suficiente
            }
        }
        
        $this->actualizarTotales($idCarrito);
        return true;
    }

    // 4. Actualizar los totales
    public function actualizarTotales($idCarrito) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT SUM(subtotal) as total FROM detallecarrito WHERE idCarrito = ?");
        $sql->execute(array($idCarrito));
        $res = $sql->fetch();
        $total = $res['total'] ?? 0;

        $sql = $conexionBD->prepare("UPDATE carrito SET subtotal = ?, total = ? WHERE idCarrito = ?");
        $sql->execute(array($total, $total, $idCarrito));
    }

    // 5. Obtener los productos del carrito
    public function obtenerProductos($idCarrito) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT dc.*, p.nombre, p.codigo_interno, l.codigo_lote 
            FROM detallecarrito dc
            INNER JOIN producto p ON p.idProducto = dc.idProducto
            INNER JOIN lote l ON l.idLote = dc.idLote
            WHERE dc.idCarrito = ?
        ");
        $sql->execute(array($idCarrito));
        return $sql->fetchAll();
    }

    // 6. Eliminar un producto del carrito y liberar la reserva
    public function eliminarProducto($idDetalle, $idCarrito) {
        $conexionBD = BD::crearInstancia();
        
        // Primero obtenemos la info del detalle para saber cuánto liberar
        $sqlDet = $conexionBD->prepare("SELECT idLote, cantidad FROM detallecarrito WHERE idDetalle = ?");
        $sqlDet->execute([$idDetalle]);
        $det = $sqlDet->fetch();
        
        if ($det && $det['idLote']) {
            // Liberamos la reserva en el lote
            $sqlReserva = $conexionBD->prepare("UPDATE lote SET cantidad_reservada = GREATEST(0, cantidad_reservada - ?) WHERE idLote = ?");
            $sqlReserva->execute([$det['cantidad'], $det['idLote']]);
        }

        $sql = $conexionBD->prepare("DELETE FROM detallecarrito WHERE idDetalle = ?");
        $sql->execute(array($idDetalle));
        $this->actualizarTotales($idCarrito);
    }
}
?>