<?php
include_once("../../Controlador/controlador_" . $controlador . ".php");
$objControlador = "Controlador" . ucfirst($controlador);
$controladorObj = new $objControlador();
$controladorObj->$accion();
?>