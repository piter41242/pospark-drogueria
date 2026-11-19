<?php
// Vista/Administrador/inicio.php
require_once("../../Modelo/seguridad.php"); 

$controlador = 'usuario';
$accion      = 'inicio';

if (isset($_GET['controlador']) && isset($_GET['accion'])) {
    if ($_GET['controlador'] != "" && $_GET['accion'] != "") {
        $controlador = $_GET['controlador'];
        $accion      = $_GET['accion'];
    }
}

// =========================================================================
// MAGIA PARA AJAX: Si la acción es crear, editar o eliminar, NO cargamos el template.
// Solo llamamos al controlador para que devuelva el JSON puro y cortamos el script.
// =========================================================================
if (in_array($accion, ['crear', 'editar', 'eliminar', 'obtener', 'crearProducto', 'ingresarLote', 'ajustarStock', 'buscarProductosAjax', 'obtenerLotes'])) {
    include_once("../../Controlador/controlador_" . $controlador . ".php");
    $objControlador = "Controlador" . ucfirst($controlador);
    $controladorObj = new $objControlador();
    $controladorObj->$accion();
    exit();
}
// =========================================================================

// Si es una vista normal, cargamos el template con todo el menú
require_once("template.php");
?>