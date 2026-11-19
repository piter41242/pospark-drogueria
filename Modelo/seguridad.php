<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// FORZAR AL NAVEGADOR A NO GUARDAR EN CACHÉ LAS PÁGINAS PROTEGIDAS
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
// 1. Validar que haya una sesión iniciada
if (!isset($_SESSION['usuario']['idUsuario'])) {
    header("Location: ../../index.php?ps=login");
    exit();
}

// 2. Obtener el rol del usuario (1=Desarrollador, 2=Administrador, 3=Vendedor, 5=Superadmin)
$rol_usuario = (int)$_SESSION['usuario']['permiso'];

// 3. Detectar en qué carpeta está intentando entrar el usuario
// $_SERVER['PHP_SELF'] nos dice la ruta completa. Ej: /drogueria/Vista/Administrador/inicio.php
$ruta_actual = $_SERVER['PHP_SELF'];

// 4. Lógica de bloqueo
// Si está intentando entrar a la carpeta "Desarrollador" pero NO es Desarrollador (1)
if (strpos($ruta_actual, '/Desarrollador/') !== false && $rol_usuario !== 1) {
    header("Location: ../../index.php?ps=no_autorizado");
    exit();
}

// Si está intentando entrar a la carpeta "Administrador" pero NO es Administrador (2), Superadmin (5) ni Desarrollador (1)
if (strpos($ruta_actual, '/Administrador/') !== false && !in_array($rol_usuario, [1, 2, 5])) {
    header("Location: ../../index.php?ps=no_autorizado");
    exit();
}

// Si está intentando entrar a la carpeta "Vendedor" pero NO es Vendedor (3) ni Desarrollador (1)
if (strpos($ruta_actual, '/Vendedor/') !== false && !in_array($rol_usuario, [1, 3])) {
    header("Location: ../../index.php?ps=no_autorizado");
    exit();
}
?>