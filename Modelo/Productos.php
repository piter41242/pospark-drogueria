<?php
class Producto {
    private $idProducto;
    private $codigo_barras;
    private $codigo_interno;
    private $nombre;
    private $valorVenta;
    private $requiere_receta;

    public function __construct($idProducto = null, $codigo_barras = null, $codigo_interno = null, $nombre = null, $valorVenta = null, $requiere_receta = null) {
        $this->idProducto = $idProducto;
        $this->codigo_barras = $codigo_barras;
        $this->codigo_interno = $codigo_interno;
        $this->nombre = $nombre;
        $this->valorVenta = $valorVenta;
        $this->requiere_receta = $requiere_receta;
    }

    // Buscar producto exacto por código de barras (para el escáner)
    public function buscarPorCodigoBarras($codigo, $idSede) {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT p.*, 
                   (SELECT SUM(l.cantidad_actual - l.cantidad_reservada) 
                    FROM lote l 
                    WHERE l.idProducto = p.idProducto AND l.idSede = ? AND l.estado = 1 AND l.fecha_vencimiento >= CURDATE()) as stock_disponible
            FROM producto p 
            WHERE p.codigo_barras = ? AND p.estado = 1
        ");
        $sql->execute(array($idSede, $codigo));
        return $sql->fetch();
    }

    // Buscar productos por nombre o código interno (para búsqueda manual)
    public function buscarPorNombre($termino, $idSede) {
        $lista = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT p.*, 
                   (SELECT SUM(l.cantidad_actual - l.cantidad_reservada) 
                    FROM lote l 
                    WHERE l.idProducto = p.idProducto AND l.idSede = ? AND l.estado = 1 AND l.fecha_vencimiento >= CURDATE()) as stock_disponible
            FROM producto p 
            WHERE (p.nombre LIKE ? OR p.codigo_interno LIKE ?) AND p.estado = 1
            LIMIT 20
        ");
        $termino = "%" . $termino . "%";
        $sql->execute(array($idSede, $termino, $termino));
        foreach ($sql->fetchAll() as $row) $lista[] = $row;
        return $lista;
    }
}
?>