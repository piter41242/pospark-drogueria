<?php
class Venta {
    public function consultarPaginado($idSede, $idUsuario, $fecha, $busqueda, $idModopago, $inicio, $limite) {
        $conexionBD = BD::crearInstancia();
        
        $where = "v.idSede = ? AND DATE(v.fecha) = ?";
        $params = [$idSede, $fecha];

        if ($idUsuario !== null) {
            $where .= " AND v.usuario = ?";
            $params[] = $idUsuario;
        }

        if (!empty($busqueda)) {
            $where .= " AND (c.nombre LIKE ? OR v.idVenta LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        if (!empty($idModopago)) {
            $where .= " AND v.modopago = ?";
            $params[] = $idModopago;
        }

        $sql = $conexionBD->prepare("
            SELECT v.*, c.nombre AS cliente, u.nombre AS vendedor, mp.nombre AS metodo_pago
            FROM venta v
            LEFT JOIN cliente c ON c.idCliente = v.idCliente
            LEFT JOIN usuario u ON u.idUsuario = v.usuario
            LEFT JOIN modopago mp ON mp.idModopago = v.modopago
            WHERE $where
            ORDER BY v.idVenta DESC
            LIMIT $inicio, $limite
        ");
        $sql->execute($params);
        return $sql->fetchAll();
    }

    public function contarTotal($idSede, $idUsuario, $fecha, $busqueda, $idModopago) {
        $conexionBD = BD::crearInstancia();
        
        $where = "v.idSede = ? AND DATE(v.fecha) = ?";
        $params = [$idSede, $fecha];

        if ($idUsuario !== null) {
            $where .= " AND v.usuario = ?";
            $params[] = $idUsuario;
        }

        if (!empty($busqueda)) {
            $where .= " AND (c.nombre LIKE ? OR v.idVenta LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        if (!empty($idModopago)) {
            $where .= " AND v.modopago = ?";
            $params[] = $idModopago;
        }

        $sql = $conexionBD->prepare("
            SELECT COUNT(*) as total 
            FROM venta v
            LEFT JOIN cliente c ON c.idCliente = v.idCliente
            WHERE $where
        ");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'];
    }

    public function calcularRecaudo($idSede, $idUsuario, $fecha) {
        $conexionBD = BD::crearInstancia();
        
        $where = "v.idSede = ? AND DATE(v.fecha) = ? AND v.modopago != 2"; // Excluimos créditos
        $params = [$idSede, $fecha];

        if ($idUsuario !== null) {
            $where .= " AND v.usuario = ?";
            $params[] = $idUsuario;
        }

        $sql = $conexionBD->prepare("SELECT SUM(v.total) as total FROM venta v WHERE $where");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'] ?? 0;
    }

    public function obtenerDetalleVenta($idVenta) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT dv.*, p.nombre AS producto, l.codigo_lote
            FROM detalleventa dv
            INNER JOIN producto p ON p.idProducto = dv.idProducto
            LEFT JOIN lote l ON l.idLote = dv.idLote
            WHERE dv.idVenta = ?
        ");
        $sql->execute([$idVenta]);
        return $sql->fetchAll();
    }

    public function consultarMetodosPago() {
        $conexionBD = BD::crearInstancia();
        return $conexionBD->query("SELECT * FROM modopago ORDER BY idModopago")->fetchAll();
    }
}
?>