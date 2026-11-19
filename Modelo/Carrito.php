<?php
class Carrito {
    
    // 1. Abrir un carrito nuevo para un cliente
    public function abrir($idSede, $idCliente, $idUsuario) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("INSERT INTO carrito (idSede, idCliente, usuario_apertura, estado) VALUES (?, ?, ?, 'ABIERTO')");
        $sql->execute(array($idSede, $idCliente, $idUsuario));
        return $conexionBD->lastInsertId();
    }

    // 2. Buscar el carrito abierto de un usuario
    public function buscarCarritoAbierto($idUsuario, $idSede) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM carrito WHERE usuario_apertura = ? AND idSede = ? AND estado = 'ABIERTO' ORDER BY fecha_apertura DESC LIMIT 1");
        $sql->execute(array($idUsuario, $idSede));
        return $sql->fetch();
    }

    // 3. Agregar producto al carrito
    public function agregarProducto($idCarrito, $idProducto, $cantidad, $valor, $idUsuario) {
        $conexionBD = BD::crearInstancia();
        
        // Verificar si el producto ya está en el carrito
        $sql = $conexionBD->prepare("SELECT * FROM detallecarrito WHERE idCarrito = ? AND idProducto = ?");
        $sql->execute(array($idCarrito, $idProducto));
        $existe = $sql->fetch();

        if ($existe) {
            // Si ya existe, sumamos la cantidad
            $nuevaCantidad = $existe['cantidad'] + $cantidad;
            $nuevoSubtotal = $nuevaCantidad * $valor;
            $sql = $conexionBD->prepare("UPDATE detallecarrito SET cantidad = ?, subtotal = ? WHERE idDetalle = ?");
            $sql->execute(array($nuevaCantidad, $nuevoSubtotal, $existe['idDetalle']));
        } else {
            // Si no existe, lo insertamos
            $subtotal = $cantidad * $valor;
            $sql = $conexionBD->prepare("INSERT INTO detallecarrito (idCarrito, idProducto, cantidad, valor, subtotal, agregado_por) VALUES (?, ?, ?, ?, ?, ?)");
            $sql->execute(array($idCarrito, $idProducto, $cantidad, $valor, $subtotal, $idUsuario));
        }
        
        $this->actualizarTotales($idCarrito);
        return true;
    }

    // 4. Actualizar los totales de la cabecera del carrito
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
            SELECT dc.*, p.nombre, p.codigo_interno 
            FROM detallecarrito dc
            INNER JOIN producto p ON p.idProducto = dc.idProducto
            WHERE dc.idCarrito = ?
        ");
        $sql->execute(array($idCarrito));
        return $sql->fetchAll();
    }

    // 6. Eliminar un producto del carrito
    public function eliminarProducto($idDetalle, $idCarrito) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("DELETE FROM detallecarrito WHERE idDetalle = ?");
        $sql->execute(array($idDetalle));
        $this->actualizarTotales($idCarrito);
    }
}
?>