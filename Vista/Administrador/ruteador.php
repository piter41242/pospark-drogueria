<?php
// Detectar si la petición viene por AJAX (crear, editar, eliminar)
$esAjax = in_array($accion, ['crear', 'editar', 'eliminar', 'obtener']);

// Si es AJAX, NO cargamos el HTML del template, solo ejecutamos el controlador
if ($esAjax) {
    include_once("../../Controlador/controlador_" . $controlador . ".php");
    $objControlador = "Controlador" . ucfirst($controlador);
    $controladorObj = new $objControlador();
    $controladorObj->$accion();
    exit(); // Detenemos la ejecución para que no cargue nada más
}

// Si es una vista normal (inicio, etc), sí cargamos el controlador normal
include_once("../../Controlador/controlador_" . $controlador . ".php");
$objControlador = "Controlador" . ucfirst($controlador);
$controladorObj = new $objControlador();
$controladorObj->$accion();
?>