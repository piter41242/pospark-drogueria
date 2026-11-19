<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Usuario.php");
include_once(__DIR__ . "/../Modelo/Permiso.php");
include_once(__DIR__ . "/../Modelo/Sede.php");
include_once(__DIR__ . "/../Modelo/Negocio.php");
error_reporting(0);
ini_set('display_errors', 0);
BD::crearInstancia();

class ControladorUsuario
{
    private $modeloUsuario;

    public function __construct()
    {
        $this->modeloUsuario = new Usuario();
    }

    public function inicio()
    {
        if ($_SESSION['usuario']['permiso'] == 1) {
            header("Location:?controlador=usuario&accion=empresas&dive=l3");
            exit();
        }

        $datosDashboard = $this->modeloUsuario->obtenerResumenDashboard($_SESSION['usuario']['idSede']);
        $stockBajo = $datosDashboard['stockBajo'];
        $porVencer = $datosDashboard['porVencer'];
        $carritos  = $datosDashboard['carritos'];

        $rol = $_SESSION['usuario']['permiso'];
        if ($rol == 2 || $rol == 5) {
            include_once(__DIR__ . "/../Vista/Administrador/entrada.php");
        } else {
            include_once(__DIR__ . "/../Vista/Vendedor/entrada.php");
        }
    }

    public function cerrarSesion()
    {
        session_unset();
        session_destroy();
        echo "<script>
            window.location.replace('../../index.php');
            history.pushState(null, null, document.URL);
            window.addEventListener('popstate', function () {
                history.pushState(null, null, document.URL);
            });
        </script>";
        exit();
    }

    public function usuario()
    {
        // Si es Desarrollador (1), lo mandamos a empresas
        if ($_SESSION['usuario']['permiso'] == 1) {
            header("Location:?controlador=usuario&accion=empresas&dive=l3");
            exit();
        }

        $rol_actual = (int)$_SESSION['usuario']['permiso']; // 2 = Admin Sede, 5 = Superadmin

        $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina < 1) $pagina = 1;

        $porPagina = 10;
        $inicio = ($pagina - 1) * $porPagina;

        $idSede = $_SESSION['usuario']['idSede'];
        $usuarios = $this->modeloUsuario->consultarPaginado($idSede, $busqueda, $inicio, $porPagina);
        $totalRegistros = $this->modeloUsuario->contarTotal($idSede, $busqueda);
        $totalPaginas = ceil($totalRegistros / $porPagina);

        // Si es Superadmin (5), traemos las sedes de su negocio para el select
        $sedesDisponibles = [];
        if ($rol_actual == 5) {
            $modeloSede = new Sede();
            $sedeAdmin = $modeloSede->buscarId($_SESSION['usuario']['idSede']);
            $idNegocio = $sedeAdmin['idNegocio'] ?? 1;
            $sedesDisponibles = $modeloSede->consultarPorNegocio($idNegocio);
        }

        include_once(__DIR__ . "/../Vista/Administrador/usuario.php");
    }

    public function crear()
    {
        header('Content-Type: application/json');
        if ($_SESSION['usuario']['permiso'] == 1) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre   = trim($_POST['nombre'] ?? '');
                $usuario  = trim($_POST['usuario'] ?? '');
                $clave    = $_POST['clave'] ?? '';
                $telefono = trim($_POST['telefono'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $permiso1 = $_POST['permiso'] ?? 3;
                $estado   = $_POST['estado'] ?? 1;

                // Lógica de Sede: si es Superadmin (2), recibe la sede. Si no, se le fuerza la suya.
                if ($_SESSION['usuario']['permiso'] == 5 && isset($_POST['idSede'])) {
                    $idSede = (int)$_POST['idSede'];
                } else {
                    $idSede = $_SESSION['usuario']['idSede']; // Admin normal (2) crea para su propia sede
                }

                if ($nombre === '' || $usuario === '' || $clave === '') {
                    echo json_encode(['status' => 'error', 'msg' => 'Nombre, usuario y contraseña son obligatorios']);
                    exit();
                }

                if ($this->modeloUsuario->existeUsuario($usuario)) {
                    echo json_encode(['status' => 'error', 'msg' => 'El nombre de usuario ya está en uso.']);
                    exit();
                }

                $claveHash = password_hash($clave, PASSWORD_DEFAULT);

                if ($this->modeloUsuario->crear($nombre, $usuario, $claveHash, $telefono, $email, $permiso1, $idSede, $estado)) {
                    if (!empty($email)) {
                        require_once(__DIR__ . "/../Modelo/EnviarCorreo.php");
                        EnviarCorreo::notificarUsuario($email, $nombre, 'crear');
                    }
                    echo json_encode(['status' => 'success', 'msg' => 'Usuario creado correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo crear el usuario']);
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
        if ($_SESSION['usuario']['permiso'] == 1) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        $idUsuario = $_POST['idUsuario'] ?? $_GET['id'] ?? 0;
        $usuarioDb = $this->modeloUsuario->buscarIdInner($idUsuario);

        if (!$usuarioDb) {
            echo json_encode(['status' => 'error', 'msg' => 'Usuario no encontrado']);
            exit();
        }

        if ((int)$usuarioDb['idSede'] !== (int)$_SESSION['usuario']['idSede']) {
            echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre   = trim($_POST['nombre'] ?? '');
                $usuario  = trim($_POST['usuario'] ?? '');
                $clave    = $_POST['clave'] ?? '';
                $telefono = trim($_POST['telefono'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $permiso1 = $_POST['permiso'] ?? 3;
                $estado   = $_POST['estado'] ?? 1;

                if ($_SESSION['usuario']['permiso'] == 5 && isset($_POST['idSede'])) {
                    $idSede = (int)$_POST['idSede'];
                } else {
                    $idSede = $usuarioDb['idSede'];
                }

                if ($this->modeloUsuario->existeUsuario($usuario, $idUsuario)) {
                    echo json_encode(['status' => 'error', 'msg' => 'El nombre de usuario ya está en uso por otra persona.']);
                    exit();
                }

                if ($clave === '') {
                    $claveHash = $usuarioDb['clave'];
                } else {
                    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                }

                if ($this->modeloUsuario->modificar($idUsuario, $nombre, $usuario, $claveHash, $telefono, $email, $permiso1, $idSede, $estado)) {
                    if (!empty($email)) {
                        require_once(__DIR__ . "/../Modelo/EnviarCorreo.php");
                        EnviarCorreo::notificarUsuario($email, $nombre, 'editar');
                    }
                    echo json_encode(['status' => 'success', 'msg' => 'Usuario actualizado correctamente.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'msg' => 'No se pudo actualizar el usuario']);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'msg' => 'Error del servidor: ' . $e->getMessage()]);
                exit();
            }
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($id == $_SESSION['usuario']['idUsuario']) {
            echo json_encode(['status' => 'error', 'msg' => 'No puedes eliminar tu propio usuario.']);
            exit();
        }

        if ($this->modeloUsuario->borrar($id)) {
            echo json_encode(['status' => 'success', 'msg' => 'Usuario eliminado correctamente.']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar el usuario.']);
            exit();
        }
    }

    public function empresas()
    {
        if ($_SESSION['usuario']['permiso'] != 1) {
            header("Location:../../index.php?ps=login");
            exit();
        }
        $modeloNegocio = new Negocio();
        $empresas = $modeloNegocio->consultarTodos();
        include_once(__DIR__ . "/../Vista/Desarrollador/empresas.php");
    }
}
