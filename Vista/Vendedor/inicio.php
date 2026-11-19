<?php
require_once("../../Modelo/seguridad.php"); 

$controlador = 'usuario';
$accion      = 'inicio';

if (isset($_GET['controlador']) && isset($_GET['accion'])) {
    if ($_GET['controlador'] != "" && $_GET['accion'] != "") {
        $controlador = $_GET['controlador'];
        $accion      = $_GET['accion'];
    }
}

// Lista de acciones AJAX para el Vendedor (POS)
if (in_array($accion, ['crear', 'editar', 'eliminar', 'obtener', 'buscarProductosAjax', 'agregarAlCarrito', 'quitarDelCarrito', 'cobrarVenta', 'abrirCarrito', 'cerrarCaja'])) {
    include_once("../../Controlador/controlador_" . $controlador . ".php");
    $objControlador = "Controlador" . ucfirst($controlador);
    $controladorObj = new $objControlador();
    $controladorObj->$accion();
    exit();
}

require_once("template.php");
?>