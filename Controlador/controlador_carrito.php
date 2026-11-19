<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Carrito.php");
include_once(__DIR__ . "/../Modelo/Productos.php");

error_reporting(0);
ini_set('display_errors', 0);
BD::crearInstancia();

class ControladorCarrito {
    private $modeloCarrito;

    public function __construct() {
        $this->modeloCarrito = new Carrito();
    }

    public function inicio() {
        $idSede = $_SESSION['usuario']['idSede'];
        $idUsuario = $_SESSION['usuario']['idUsuario'];

        // Buscamos si el vendedor ya tiene un carrito abierto
        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);
        
        // Si no tiene, le creamos uno
        if (!$carrito) {
            $idClienteFinal = 1; // ID del Consumidor Final
            $idCarrito = $this->modeloCarrito->abrir($idSede, $idClienteFinal, $idUsuario);
            $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);
        } else {
            $idCarrito = $carrito['idCarrito'];
        }

        // Obtenemos los productos que ya tenga en el carrito
        $productos = $this->modeloCarrito->obtenerProductos($idCarrito);
        
        $idCarritoActual = $idCarrito;
        include_once(__DIR__ . "/../Vista/Vendedor/pos.php");
    }

    // --- PETICIONES AJAX ---

    public function agregarAlCarrito() {
        header('Content-Type: application/json');
        $idProducto = $_POST['idProducto'] ?? 0;
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);
        
        if ($carrito && $idProducto > 0) {
            if ($this->modeloCarrito->agregarProducto($carrito['idCarrito'], $idProducto, $cantidad, $idUsuario)) {
                echo json_encode(['status' => 'success']); exit();
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No hay stock suficiente o el producto está agotado.']); exit();
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'No se encontró un carrito abierto.']); exit();
    }

    public function eliminarDelCarrito() {
        header('Content-Type: application/json');
        $idDetalle = $_POST['idDetalle'] ?? 0;
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);
        
        if ($carrito && $idDetalle > 0) {
            $this->modeloCarrito->eliminarProducto($idDetalle, $carrito['idCarrito']);
            echo json_encode(['status' => 'success']); exit();
        }
        echo json_encode(['status' => 'error']); exit();
    }
}
?>