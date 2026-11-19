<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Sede.php");
include_once(__DIR__ . "/../Modelo/Negocio.php");
error_reporting(0);
ini_set('display_errors', 0);

BD::crearInstancia();

class ControladorSede
{

    private $modeloSede;

    public function __construct()
    {
        $this->modeloSede = new Sede();
    }

    // Devuelve el idNegocio del Admin logueado; null para Desarrollador
    private function idNegocioActual()
    {
        $rol = (int)$_SESSION['usuario']['permiso'];
        if ($rol === 1) return null;
        $sede = $this->modeloSede->buscarId($_SESSION['usuario']['idSede']);
        return $sede ? (int)$sede['idNegocio'] : null;
    }

    public function inicio()
    {
        $rol = (int)$_SESSION['usuario']['permiso'];
        // Permitimos Desarrollador (1), Admin (2) y Superadmin (5)
        if (!in_array($rol, [1, 2, 5])) {
            header("Location:?controlador=usuario&accion=inicio&dive=l2");
            exit();
        }

        $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina < 1) $pagina = 1;

        $porPagina = 10;
        $inicio = ($pagina - 1) * $porPagina;

        if ($rol === 1) {
            $idNegocio = null; // Desarrollador ve todo
        } else {
            // Admin y Superadmin solo ven las sedes de su negocio
            $sedeAdmin = $this->modeloSede->buscarId($_SESSION['usuario']['idSede']);
            $idNegocio = $sedeAdmin ? (int)$sedeAdmin['idNegocio'] : -1;
        }

        $sedes = $this->modeloSede->consultarPaginado($idNegocio, $busqueda, $inicio, $porPagina);
        $totalRegistros = $this->modeloSede->contarTotal($idNegocio, $busqueda);
        $totalPaginas = ceil($totalRegistros / $porPagina);

        // Pasamos el rol a la vista
        $rol_actual = $rol;

        include_once(__DIR__ . "/../Vista/Administrador/sedes.php");
    }

    public function crear()
    {
        // Le decimos al navegador que la respuesta será JSON
        header('Content-Type: application/json');

        $rol = (int)$_SESSION['usuario']['permiso'];
        if (!in_array($rol, [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idNegocio   = $_POST['idNegocio'] ?? null;
                $nombre      = trim($_POST['nombre'] ?? '');
                $direccion   = trim($_POST['direccion'] ?? '');
                $telefono    = trim($_POST['telefono'] ?? '');
                $ciudad      = trim($_POST['ciudad'] ?? '');
                $codigo_dane = trim($_POST['codigo_dane'] ?? '') ?: null;
                $estado      = $_POST['estado'] ?? 1;

                if (!$idNegocio || $nombre === '' || $direccion === '') {
                    echo json_encode(['status' => 'error', 'msg' => 'Nombre, dirección y negocio son obligatorios']);
                    exit();
                }

                if ($rol === 2 && (int)$idNegocio !== $this->idNegocioActual()) {
                    echo json_encode(['status' => 'error', 'msg' => 'No autorizado para este negocio']);
                    exit();
                }
                // Validación de duplicados
                if ($this->modeloSede->existeSede($nombre, $idNegocio)) {
                    echo json_encode(['status' => 'error', 'msg' => 'Ya existe una sede con este nombre en este negocio.']);
                    exit();
                }
                if ($this->modeloSede->crear($idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado)) {
                    // Devolvemos el ID insertado para el DOM
                    $nuevoId = BD::crearInstancia()->lastInsertId();
                    echo json_encode(['status' => 'success', 'msg' => 'Sede creada correctamente.', 'id' => $nuevoId]);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo crear la sede']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error del servidor: ' . $e->getMessage()]);
                exit();
            }
        }
    }

    public function editar()
    {
        header('Content-Type: application/json');

        $rol = (int)$_SESSION['usuario']['permiso'];
        if (!in_array($rol, [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        $idSede = $_POST['idSede'] ?? $_GET['id'] ?? 0;
        $sede = $this->modeloSede->buscarId($idSede);

        if (!$sede) {
            echo json_encode(['status' => 'error', 'msg' => 'Sede no encontrada']);
            exit();
        }
        if ($rol === 2 && (int)$sede['idNegocio'] !== $this->idNegocioActual()) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idNegocio   = $_POST['idNegocio'] ?? null;
                $nombre      = trim($_POST['nombre'] ?? '');
                $direccion   = trim($_POST['direccion'] ?? '');
                $telefono    = trim($_POST['telefono'] ?? '');
                $ciudad      = trim($_POST['ciudad'] ?? '');
                $codigo_dane = trim($_POST['codigo_dane'] ?? '') ?: null;
                $estado      = $_POST['estado'] ?? 1;

                if ($rol === 2 && (int)$idNegocio !== $this->idNegocioActual()) {
                    echo json_encode(['status' => 'error', 'msg' => 'No autorizado para este negocio']);
                    exit();
                }
                // Validación de duplicados
                if ($this->modeloSede->existeSede($nombre, $idNegocio, $idSede)) {
                    echo json_encode(['status' => 'error', 'msg' => 'Ya existe otra sede con este nombre en este negocio.']);
                    exit();
                }
                if ($this->modeloSede->modificar($idSede, $idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado)) {
                    echo json_encode(['status' => 'success', 'msg' => 'Sede actualizada correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo actualizar la sede']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error del servidor: ' . $e->getMessage()]);
                exit();
            }
        }

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            echo json_encode($sede);
            exit();
        }
    }

    public function eliminar()
    {
        $rol = (int)$_SESSION['usuario']['permiso'];
        if (!in_array($rol, [1, 2, 5])) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        $id = $_POST['id'] ?? 0;
        $sede = $this->modeloSede->buscarId($id);

        if (!$sede) {
            echo json_encode(['status' => 'error', 'msg' => 'Sede no encontrada']);
            exit();
        }
        if ($rol === 2 && (int)$sede['idNegocio'] !== $this->idNegocioActual()) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }
        if ((int)$id === (int)$_SESSION['usuario']['idSede']) {
            echo json_encode(['status' => 'error', 'msg' => 'No puedes eliminar la sede en la que estás logueado.']);
            exit();
        }
        if ($this->modeloSede->tieneDependencias($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Esta sede tiene registros asociados. Desactívela en lugar de borrarla.']);
            exit();
        }

        if ($this->modeloSede->borrar($id)) {
            echo json_encode(['status' => 'success', 'msg' => 'Sede eliminada correctamente.']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar la sede.']);
            exit();
        }
    }

    // Agrega este nuevo método para el editar con AJAX
    public function obtener()
    {
        $id = $_GET['id'] ?? 0;
        $sede = $this->modeloSede->buscarId($id);

        if ($sede) {
            header('Content-Type: application/json');
            echo json_encode($sede);
        } else {
            echo json_encode(['error' => 'No encontrada']);
        }
        exit();
    }
}
