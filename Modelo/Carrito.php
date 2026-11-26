<?php
class Carrito
{

    // 1. Abrir un carrito nuevo
    public function abrir($idSede, $idCliente, $idUsuario)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            INSERT INTO carrito (idSede, idCliente, usuario_apertura, estado, subtotal, descuento, impuesto, total) 
            VALUES (?, ?, ?, 'ABIERTO', 0, 0, 0, 0)
        ");
        $sql->execute(array($idSede, $idCliente, $idUsuario));
        return $conexionBD->lastInsertId();
    }

    // 2. Buscar el carrito abierto del usuario
    public function buscarCarritoAbierto($idUsuario, $idSede)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM carrito WHERE usuario_apertura = ? AND idSede = ? AND estado = 'ABIERTO' ORDER BY fecha_apertura DESC LIMIT 1");
        $sql->execute(array($idUsuario, $idSede));
        return $sql->fetch();
    }

    // 3. Agregar producto al carrito (Buscando el lote FIFO)
    public function agregarProducto($idCarrito, $idProducto, $cantidad, $idUsuario)
    {
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
    public function actualizarTotales($idCarrito)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT SUM(subtotal) as total FROM detallecarrito WHERE idCarrito = ?");
        $sql->execute(array($idCarrito));
        $res = $sql->fetch();
        $total = $res['total'] ?? 0;

        $sql = $conexionBD->prepare("UPDATE carrito SET subtotal = ?, total = ? WHERE idCarrito = ?");
        $sql->execute(array($total, $total, $idCarrito));
    }

    // 5. Obtener los productos del carrito
    public function obtenerProductos($idCarrito)
    {
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
    public function eliminarProducto($idDetalle, $idCarrito)
    {
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
    public function modificarCantidad($idDetalle, $idCarrito, $cambio)
    {
        $conexionBD = BD::crearInstancia();

        $sql = $conexionBD->prepare("SELECT idProducto, idLote, cantidad, valor FROM detallecarrito WHERE idDetalle = ?");
        $sql->execute([$idDetalle]);
        $det = $sql->fetch();

        if (!$det) return false;

        $nuevaCantidad = $det['cantidad'] + $cambio;

        // Si llega a 0, se elimina
        if ($nuevaCantidad <= 0) {
            $this->eliminarProducto($idDetalle, $idCarrito);
            return true;
        }

        // Validar stock si es suma
        if ($cambio > 0) {
            $sqlLote = $conexionBD->prepare("SELECT (cantidad_actual - cantidad_reservada) as disp FROM lote WHERE idLote = ?");
            $sqlLote->execute([$det['idLote']]);
            $lote = $sqlLote->fetch();

            if (!$lote || $lote['disp'] < $cambio) {
                return false;
            }
        }

        // Actualizar detalle
        $nuevoSubtotal = $nuevaCantidad * $det['valor'];
        $sqlUpdate = $conexionBD->prepare("UPDATE detallecarrito SET cantidad = ?, subtotal = ? WHERE idDetalle = ?");
        $sqlUpdate->execute([$nuevaCantidad, $nuevoSubtotal, $idDetalle]);

        // Actualizar reserva del lote
        $sqlReserva = $conexionBD->prepare("UPDATE lote SET cantidad_reservada = cantidad_reservada + ? WHERE idLote = ?");
        $sqlReserva->execute([$cambio, $det['idLote']]);

        $this->actualizarTotales($idCarrito);
        return true;
    }
    public function cobrarVenta($idCarrito, $idUsuario, $idSede, $modopago, $pagacon, $medio_abono = null)
    {
        $conexionBD = BD::crearInstancia();
        $conexionBD->beginTransaction();

        try {
            $sqlCarrito = $conexionBD->prepare("SELECT * FROM carrito WHERE idCarrito = ?");
            $sqlCarrito->execute([$idCarrito]);
            $carrito = $sqlCarrito->fetch();

            if (!$carrito || $carrito['estado'] !== 'ABIERTO') {
                throw new Exception("El carrito no está abierto.");
            }

            $total = $carrito['total'];
            $cambio = 0;

            // Si es efectivo, calculamos el cambio
            if ($modopago == 1) {
                $cambio = $pagacon > $total ? ($pagacon - $total) : 0;
            } else if ($modopago != 2) {
                // Si es transferencia, asumimos que pagó exacto
                $pagacon = $total;
            }

            // 1. Registrar la Venta (Cabecera)
            $sqlVenta = $conexionBD->prepare("
                INSERT INTO venta (idCarrito, idSede, idCliente, usuario, subtotal, descuento, impuesto, total, pagacon, cambio, modopago, tipo_comprobante, factura_electronica, estado_dian) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'POS', 0, 'NO_APLICA')
            ");
            $sqlVenta->execute([
                $idCarrito,
                $idSede,
                $carrito['idCliente'],
                $idUsuario,
                $carrito['subtotal'],
                $carrito['descuento'],
                $carrito['impuesto'],
                $total,
                $pagacon,
                $cambio,
                $modopago
            ]);
            $idVenta = $conexionBD->lastInsertId();

            // 2. Registrar el Pago Inicial en la tabla pago_venta
            if ($modopago == 2 && $pagacon > 0) {
                // Si es crédito pero dieron abono inicial
                $sqlPagoIni = $conexionBD->prepare("INSERT INTO pago_venta (idVenta, idModopago, monto) VALUES (?, ?, ?)");
                $sqlPagoIni->execute([$idVenta, $medio_abono, $pagacon]);
            } else if ($modopago != 2) {
                // Si es efectivo o transferencia, se pagó todo
                $sqlPagoIni = $conexionBD->prepare("INSERT INTO pago_venta (idVenta, idModopago, monto) VALUES (?, ?, ?)");
                $sqlPagoIni->execute([$idVenta, $modopago, $total]);
            }

            // 3. Si es a Crédito, creamos la deuda
            if ($modopago == 2) {
                $saldoPendiente = $total - $pagacon;
                $sqlCredito = $conexionBD->prepare("
                    INSERT INTO credito (idVenta, idCliente, idSede, monto_total, saldo_pendiente, estado) 
                    VALUES (?, ?, ?, ?, ?, 'PENDIENTE')
                ");
                $sqlCredito->execute([$idVenta, $carrito['idCliente'], $idSede, $total, $saldoPendiente]);
            }

            // 4. Descontar Stock y guardar Kardex
            $sqlDetalle = $conexionBD->prepare("SELECT * FROM detallecarrito WHERE idCarrito = ?");
            $sqlDetalle->execute([$idCarrito]);
            $detalles = $sqlDetalle->fetchAll();

            foreach ($detalles as $det) {
                $sqlDetVenta = $conexionBD->prepare("
                    INSERT INTO detalleventa (idVenta, idProducto, idLote, cantidad, valor, descuento, subtotal, impuesto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                ");
                $sqlDetVenta->execute([
                    $idVenta,
                    $det['idProducto'],
                    $det['idLote'],
                    $det['cantidad'],
                    $det['valor'],
                    $det['descuento'],
                    $det['subtotal']
                ]);

                $sqlLote = $conexionBD->prepare("UPDATE lote SET cantidad_actual = cantidad_actual - ?, cantidad_reservada = GREATEST(0, cantidad_reservada - ?) WHERE idLote = ?");
                $sqlLote->execute([$det['cantidad'], $det['cantidad'], $det['idLote']]);

                $sqlKardex = $conexionBD->prepare("
                    INSERT INTO kardex (idProducto, idLote, idSede, tipo, cantidad, saldo, referencia, idReferencia, usuario, fecha, observacion) 
                    SELECT idProducto, idLote, idSede, 'SALIDA', ?, cantidad_actual, 'VENTA', ?, ?, NOW(), 'Venta POS' 
                    FROM lote WHERE idLote = ?
                ");
                $sqlKardex->execute([$det['cantidad'], $idVenta, $idUsuario, $det['idLote']]);
            }

            // 5. Cerrar el carrito
            $sqlCerrar = $conexionBD->prepare("UPDATE carrito SET estado = 'PAGADO', fecha_cierre = NOW(), usuario_cierre = ? WHERE idCarrito = ?");
            $sqlCerrar->execute([$idUsuario, $idCarrito]);

            $conexionBD->commit();
            return $idVenta;
        } catch (Exception $e) {
            $conexionBD->rollBack();
            return false;
        }
    }
    public function consultarMetodosPago()
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->query("SELECT * FROM modopago ORDER BY idModopago ASC");
        return $sql->fetchAll();
    }
    // Listar todos los carritos abiertos de la sede (para retomarlos o anularlos)
    public function consultarCarritosAbiertos($idSede)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT c.*, cl.nombre AS cliente, u.nombre AS vendedor
            FROM carrito c
            INNER JOIN cliente cl ON cl.idCliente = c.idCliente
            INNER JOIN usuario u ON u.idUsuario = c.usuario_apertura
            WHERE c.idSede = ? AND c.estado = 'ABIERTO' AND c.total > 0
            ORDER BY c.fecha_apertura DESC
        ");
        $sql->execute([$idSede]);
        return $sql->fetchAll();
    }

    // Anular un carrito (liberar stock y marcar como anulado)
    public function anularCarrito($idCarrito)
    {
        $conexionBD = BD::crearInstancia();
        $conexionBD->beginTransaction();

        try {
            // 1. Liberar el stock reservado de cada producto
            $sqlDet = $conexionBD->prepare("SELECT idLote, cantidad FROM detallecarrito WHERE idCarrito = ?");
            $sqlDet->execute([$idCarrito]);
            $detalles = $sqlDet->fetchAll();

            foreach ($detalles as $det) {
                if ($det['idLote']) {
                    $sqlReserva = $conexionBD->prepare("UPDATE lote SET cantidad_reservada = GREATEST(0, cantidad_reservada - ?) WHERE idLote = ?");
                    $sqlReserva->execute([$det['cantidad'], $det['idLote']]);
                }
            }

            // 2. Eliminar los detalles del carrito
            $sqlDel = $conexionBD->prepare("DELETE FROM detallecarrito WHERE idCarrito = ?");
            $sqlDel->execute([$idCarrito]);

            // 3. Marcar el carrito como ANULADO
            $sqlCerrar = $conexionBD->prepare("UPDATE carrito SET estado = 'ANULADO', total = 0, subtotal = 0 WHERE idCarrito = ?");
            $sqlCerrar->execute([$idCarrito]);

            $conexionBD->commit();
            return true;
        } catch (Exception $e) {
            $conexionBD->rollBack();
            return false;
        }
    }
        // Dejar el carrito en espera (liberarlo del vendedor actual)
    public function dejarEnEspera($idCarrito) {
        $conexionBD = BD::crearInstancia();
        // Al poner el usuario en NULL, el vendedor actual ya no lo tendrá como "su carrito abierto"
        $sql = $conexionBD->prepare("UPDATE carrito SET usuario_apertura = NULL WHERE idCarrito = ?");
        return $sql->execute([$idCarrito]);
    }
}
