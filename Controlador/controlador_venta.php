<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Venta.php");

error_reporting(0);
ini_set('display_errors', 0);
BD::crearInstancia();

class ControladorVenta {
    private $modeloVenta;

    public function __construct() {
        $this->modeloVenta = new Venta();
    }

    public function inicio() {
        $idSede = $_SESSION['usuario']['idSede'];
        $rol = (int)$_SESSION['usuario']['permiso'];
        $idUsuario = ($rol == 3) ? $_SESSION['usuario']['idUsuario'] : null;

        // Filtros
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $busqueda = $_GET['buscar'] ?? '';
        $idModopago = $_GET['modopago'] ?? '';

        // Paginación
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina < 1) $pagina = 1;
        $porPagina = 10;
        $inicio = ($pagina - 1) * $porPagina;

        $ventas = $this->modeloVenta->consultarPaginado($idSede, $idUsuario, $fecha, $busqueda, $idModopago, $inicio, $porPagina);
        $totalRegistros = $this->modeloVenta->contarTotal($idSede, $idUsuario, $fecha, $busqueda, $idModopago);
        $totalPaginas = ceil($totalRegistros / $porPagina);
        
        // Total recaudado (Solo efectivo y transferencias)
        $totalDia = $this->modeloVenta->calcularRecaudo($idSede, $idUsuario, $fecha);

        // Métodos de pago para el select
        $modopagos = $this->modeloVenta->consultarMetodosPago();

        include_once(__DIR__ . "/../Vista/Vendedor/ventas.php");
    }

    public function verDetalle() {
        header('Content-Type: application/json');
        $idVenta = $_GET['idVenta'] ?? 0;
        
        if ($idVenta <= 0) { echo json_encode([]); exit(); }

        $detalles = $this->modeloVenta->obtenerDetalleVenta($idVenta);
        echo json_encode($detalles);
        exit();
    }
}
?>