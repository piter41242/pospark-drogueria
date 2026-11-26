<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Carrito.php");
include_once(__DIR__ . "/../Modelo/Productos.php");

error_reporting(0);
ini_set('display_errors', 0);
BD::crearInstancia();

class ControladorCarrito
{
    private $modeloCarrito;

    public function __construct()
    {
        $this->modeloCarrito = new Carrito();
    }

    public function inicio()
    {
        $idSede = $_SESSION['usuario']['idSede'];
        $idUsuario = $_SESSION['usuario']['idUsuario'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if (!$carrito) {
            $idClienteFinal = 1;
            $idCarrito = $this->modeloCarrito->abrir($idSede, $idClienteFinal, $idUsuario);
            $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);
        } else {
            $idCarrito = $carrito['idCarrito'];
        }

        $productos = $this->modeloCarrito->obtenerProductos($idCarrito);
        $modopagos = $this->modeloCarrito->consultarMetodosPago();

        // NUEVO: Pasamos los carritos abiertos para mostrar el badge
        $carritosAbiertos = $this->modeloCarrito->consultarCarritosAbiertos($idSede);

        $idCarritoActual = $idCarrito;

        include_once(__DIR__ . "/../Vista/Vendedor/pos.php");
    }


    public function agregarAlCarrito()
    {
        header('Content-Type: application/json');
        $idProducto = $_POST['idProducto'] ?? 0;
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if ($carrito && $idProducto > 0) {
            if ($this->modeloCarrito->agregarProducto($carrito['idCarrito'], $idProducto, $cantidad, $idUsuario)) {
                echo json_encode(['status' => 'success']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No hay stock suficiente o el producto está agotado.']);
                exit();
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'No se encontró un carrito abierto.']);
        exit();
    }

    public function eliminarDelCarrito()
    {
        header('Content-Type: application/json');
        $idDetalle = $_POST['idDetalle'] ?? 0;
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if ($carrito && $idDetalle > 0) {
            $this->modeloCarrito->eliminarProducto($idDetalle, $carrito['idCarrito']);
            echo json_encode(['status' => 'success']);
            exit();
        }
        echo json_encode(['status' => 'error']);
        exit();
    }
    public function modificarCantidad()
    {
        header('Content-Type: application/json');

        // Forzamos a que no se impriman errores de PHP que dañen el JSON
        error_reporting(0);

        $idDetalle = $_POST['idDetalle'] ?? 0;
        $cambio = (int)($_POST['cambio'] ?? 0);
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if ($carrito && $idDetalle > 0) {
            if ($this->modeloCarrito->modificarCantidad($idDetalle, $carrito['idCarrito'], $cambio)) {
                echo json_encode(['status' => 'success']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No hay stock suficiente.']);
                exit();
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'Error al modificar.']);
        exit();
    }
    public function cobrarVenta()
    {
        header('Content-Type: application/json');
        error_reporting(0);

        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];
        $modopago = $_POST['modopago'] ?? 1;
        $pagacon = (float)($_POST['pagacon'] ?? 0);
        $medio_abono = isset($_POST['medio_abono']) ? (int)$_POST['medio_abono'] : null;

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if ($carrito) {
            $idVenta = $this->modeloCarrito->cobrarVenta($carrito['idCarrito'], $idUsuario, $idSede, $modopago, $pagacon, $medio_abono);

            if ($idVenta) {
                echo json_encode(['status' => 'success', 'msg' => 'Venta realizada con éxito.', 'idVenta' => $idVenta]);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Error al procesar el pago. Verifique el stock.']);
                exit();
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'No hay carrito abierto.']);
        exit();
    }
    public function abiertos()
    {
        $idSede = $_SESSION['usuario']['idSede'];

        // Buscamos los carritos abiertos (excluyendo el actual del vendedor si lo tiene)
        $carritos = $this->modeloCarrito->consultarCarritosAbiertos($idSede);

        include_once(__DIR__ . "/../Vista/Vendedor/carritos_abiertos.php");
    }

    public function anularCarrito()
    {
        header('Content-Type: application/json');
        error_reporting(0);

        $idCarrito = $_POST['idCarrito'] ?? 0;
        $idUsuario = $_SESSION['usuario']['idUsuario'];

        if ($idCarrito <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
            exit();
        }

        if ($this->modeloCarrito->anularCarrito($idCarrito)) {
            echo json_encode(['status' => 'success', 'msg' => 'Carrito anulado correctamente.']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'No se pudo anular el carrito.']);
            exit();
        }
    }
    public function retomar()
    {
        $idCarrito = $_GET['idCarrito'] ?? 0;
        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        if ($idCarrito > 0) {
            // 1. Verificamos si el vendedor ya tiene un carrito abierto
            $carritoActual = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

            // Si tiene uno abierto, lo anulamos para que no queden dos carritos a su nombre
            if ($carritoActual && $carritoActual['idCarrito'] != $idCarrito) {
                $this->modeloCarrito->anularCarrito($carritoActual['idCarrito']);
            }

            // 2. Reasignamos el carrito seleccionado al usuario actual
            $conexionBD = BD::crearInstancia();
            $sql = $conexionBD->prepare("UPDATE carrito SET usuario_apertura = ?, usuario_cierre = NULL WHERE idCarrito = ?");
            $sql->execute([$idUsuario, $idCarrito]);

            // Redirigimos al POS
            header("Location:?controlador=carrito&accion=inicio&dive=l3");
            exit();
        }
        header("Location:?controlador=carrito&accion=abiertos&dive=l4");
    }
    public function dejarEnEspera()
    {
        header('Content-Type: application/json');
        error_reporting(0);

        $idUsuario = $_SESSION['usuario']['idUsuario'];
        $idSede = $_SESSION['usuario']['idSede'];

        $carrito = $this->modeloCarrito->buscarCarritoAbierto($idUsuario, $idSede);

        if ($carrito) {
            if ($this->modeloCarrito->dejarEnEspera($carrito['idCarrito'])) {
                echo json_encode(['status' => 'success', 'msg' => 'Carrito guardado en espera correctamente.']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo guardar el carrito.']);
                exit();
            }
        }
        echo json_encode(['status' => 'error', 'msg' => 'No hay carrito activo.']);
        exit();
    }
}
