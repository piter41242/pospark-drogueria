<?php
include_once(__DIR__ . "/../Modelo/conexion.php");
include_once(__DIR__ . "/../Modelo/Usuario.php");

BD::crearInstancia();
session_start();

$usuario = $_POST['usuario'] ?? '';
$clave   = $_POST['clave']   ?? '';

$modeloUsuario = new Usuario();
$inicio = $modeloUsuario->buscarUsuario($usuario);

if (!empty($inicio)) {
    if ($inicio['estado'] == 2) {
        header("Location:../index.php?ps=bloqueo");
        exit();
    }

    if (password_verify($clave, $inicio['clave'])) {

        $_SESSION['usuario']['idUsuario']  = $inicio['idUsuario'];
        $_SESSION['usuario']['nombre']     = $inicio['nombre'];
        $_SESSION['usuario']['usuario']    = $inicio['usuario'];
        $_SESSION['usuario']['permiso']    = $inicio['permiso'];
        $_SESSION['usuario']['estado']     = $inicio['estado'];
        $_SESSION['usuario']['email']      = $inicio['email'];

        // Si el usuario tiene sede, la guardamos. El Desarrollador puede no tener sede o tener la 1.
        $_SESSION['usuario']['idSede']     = $inicio['idSede'] ?? 1;
        $_SESSION['usuario']['nombreSede'] = $inicio['nombreSede'] ?? 'TODAS LAS SEDES';

        switch ((int)$inicio['permiso']) {
            case 1:
                header("Location:../Vista/Desarrollador/inicio.php?controlador=usuario&accion=inicio&dive=l2");
                break;
            case 2:
            case 5: // Superadmin entra igual que el administrador
                header("Location:../Vista/Administrador/inicio.php?controlador=usuario&accion=inicio&dive=l2");
                break;
            case 3:
                header("Location:../Vista/Vendedor/inicio.php?controlador=usuario&accion=inicio&dive=l2");
                break;
            default:
                header("Location:../index.php?ps=login");
        }
        exit();
    } else {
        header("Location:../index.php?ps=login");
        exit();
    }
} else {
    header("Location:../index.php?ps=login");
    exit();
}
