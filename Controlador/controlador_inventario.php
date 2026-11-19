<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Inventario.php");

error_reporting(0);
ini_set('display_errors', 0);
BD::crearInstancia();

class ControladorInventario
{
    private $modeloInventario;

    public function __construct()
    {
        $this->modeloInventario = new Inventario();
    }

    public function inicio()
    {
        $rol = (int)$_SESSION['usuario']['permiso'];
        if (!in_array($rol, [1, 2, 3, 5])) {
            exit('No autorizado');
        }

        $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina < 1) $pagina = 1;

        $porPagina = 10;
        $inicio = ($pagina - 1) * $porPagina;
        $idSede = $_SESSION['usuario']['idSede'];

        // Esto ahora sí va a traer los 12,534 productos
        $productos = $this->modeloInventario->consultarPaginado($idSede, $busqueda, $inicio, $porPagina);
        $totalRegistros = $this->modeloInventario->contarTotal($idSede, $busqueda);
        $totalPaginas = ceil($totalRegistros / $porPagina);

        // Cargamos categorías, presentaciones, etc.
        $conexionBD = BD::crearInstancia();
        $categorias = $conexionBD->query("SELECT * FROM categoria ORDER BY nombre")->fetchAll();
        $presentaciones = $conexionBD->query("SELECT * FROM presentacion ORDER BY nombre")->fetchAll();
        $laboratorios = $conexionBD->query("SELECT * FROM laboratorio ORDER BY nombre")->fetchAll();
        $impuestos = $conexionBD->query("SELECT * FROM impuesto ORDER BY nombre")->fetchAll();
        $todosProductos = $this->modeloInventario->consultarTodosProductos();


        $rol_actual = $rol;
        include_once(__DIR__ . "/../Vista/Administrador/inventario.php");
    }

    public function crearProducto()
    {
        header('Content-Type: application/json');
        if (!in_array((int)$_SESSION['usuario']['permiso'], [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $codigo_interno = trim($_POST['codigo_interno'] ?? '');
                $nombre = trim($_POST['nombre'] ?? '');
                $valorCompra = (float)($_POST['valorCompra'] ?? 0);
                $valorVenta = (float)($_POST['valorVenta'] ?? 0);
                $idCategoria = (int)($_POST['idCategoria'] ?? 1);
                $idPresentacion = (int)($_POST['idPresentacion'] ?? 1);
                $idLaboratorio = (int)($_POST['idLaboratorio'] ?? 1);
                $idImpuesto = (int)($_POST['idImpuesto'] ?? 1);
                $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);

                if ($codigo_interno === '' || $nombre === '') {
                    echo json_encode(['status' => 'error', 'msg' => 'El código interno y el nombre son obligatorios']);
                    exit();
                }

                if ($this->modeloInventario->crearProducto($codigo_interno, $nombre, $valorCompra, $valorVenta, $idCategoria, $idPresentacion, $idLaboratorio, $idImpuesto, $stock_minimo)) {
                    echo json_encode(['status' => 'success', 'msg' => 'Producto creado correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo crear el producto (verifique que el código no exista).']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error: ' . $e->getMessage()]);
                exit();
            }
        }
    }

    public function ingresarLote()
    {
        header('Content-Type: application/json');
        if (!in_array((int)$_SESSION['usuario']['permiso'], [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idProducto = (int)($_POST['idProducto'] ?? 0);
                $codigo_lote = trim($_POST['codigo_lote'] ?? '');
                $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
                $cantidad = (int)($_POST['cantidad'] ?? 0);
                $costo_unitario = (float)($_POST['costo_unitario'] ?? 0);
                $idSede = $_SESSION['usuario']['idSede'];

                if ($idProducto <= 0 || $codigo_lote === '' || $fecha_vencimiento === '' || $cantidad <= 0) {
                    echo json_encode(['status' => 'error', 'msg' => 'Todos los campos son obligatorios']);
                    exit();
                }

                if ($this->modeloInventario->ingresarLote($idProducto, $idSede, $codigo_lote, $fecha_vencimiento, $cantidad, $costo_unitario)) {
                    echo json_encode(['status' => 'success', 'msg' => 'Mercancía ingresada correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo ingresar la mercancía.']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error: ' . $e->getMessage()]);
                exit();
            }
        }
    }
    public function ajustarStock()
    {
        header('Content-Type: application/json');
        if (!in_array((int)$_SESSION['usuario']['permiso'], [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idProducto = (int)($_POST['idProducto'] ?? 0);
                $cantidad   = (int)($_POST['cantidad'] ?? 0);
                $tipo       = $_POST['tipo'] ?? 'ENTRADA'; // ENTRADA o SALIDA
                $motivo     = trim($_POST['motivo'] ?? 'Ajuste manual');
                $idSede     = $_SESSION['usuario']['idSede'];
                $idUsuario  = $_SESSION['usuario']['idUsuario'];

                if ($idProducto <= 0 || $cantidad <= 0) {
                    echo json_encode(['status' => 'error', 'msg' => 'Cantidad inválida']);
                    exit();
                }

                if ($this->modeloInventario->ajustarStock($idProducto, $idSede, $cantidad, $tipo, $motivo, $idUsuario)) {
                    echo json_encode(['status' => 'success', 'msg' => 'Inventario ajustado correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo ajustar el inventario.']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error del servidor: ' . $e->getMessage()]);
                exit();
            }
        }
    }
    public function buscarProductosAjax()
    {
        header('Content-Type: application/json');
        $termino = $_GET['q'] ?? '';

        // Si el usuario escribe menos de 2 letras, no buscamos nada
        if (strlen($termino) < 2) {
            echo json_encode([]);
            exit();
        }

        $resultados = $this->modeloInventario->buscarProductosAjax($termino);
        echo json_encode($resultados);
        exit();
    }

    public function obtenerLotes()
    {
        header('Content-Type: application/json');
        $idProducto = $_GET['idProducto'] ?? 0;
        $idSede = $_SESSION['usuario']['idSede'];

        if ($idProducto <= 0) {
            echo json_encode([]);
            exit();
        }

        $lotes = $this->modeloInventario->consultarLotesProducto($idProducto, $idSede);
        echo json_encode($lotes);
        exit();
    }
}
