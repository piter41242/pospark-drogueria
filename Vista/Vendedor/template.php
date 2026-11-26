<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Droguería | POS</title>
    
    <link rel="stylesheet" href="../../Resources/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../Resources/sweetalert/css/sweetalert2.css">
    <link rel="stylesheet" href="../../Resources/css/menu.css">
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <div class="sidebar" id="sidebar">
        <div class="logo" style="padding: 20px 15px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <img src="../../Resources/img/empresa.jpg" alt="Logo Droguería" style="border-radius:50%; max-width: 160px; width: 100%; height: auto; margin-bottom: 10px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
            <div style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">
                <?php echo $_SESSION['usuario']['nombreSede']; ?>
            </div>
        </div>
        <ul>
            <li><a href="?controlador=usuario&accion=inicio&dive=l2" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l2')?'active':''; ?>"><i class="bi bi-house-door-fill me-2"></i> Inicio</a></li>
            
            <li><a href="?controlador=carrito&accion=inicio&dive=l3" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l3')?'active':''; ?>"><i class="bi bi-cart3 me-2"></i> POS (Ventas)</a></li>
            
            <li><a href="?controlador=carrito&accion=abiertos&dive=l4" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l4')?'active':''; ?>"><i class="bi bi-hourglass-split me-2"></i> Carritos Abiertos</a></li>
            
            <li><a href="?controlador=inventario&accion=inicio&dive=l5" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l5')?'active':''; ?>"><i class="bi bi-box-seam me-2"></i> Consultar Inventario</a></li>
            
            <li><a href="?controlador=cierre&accion=inicioVendedor&dive=l6" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l6')?'active':''; ?>"><i class="bi bi-cash-coin me-2"></i> Mi Cierre de Caja</a></li>

            <li><a href="?controlador=venta&accion=inicio&dive=l7" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l7')?'active':''; ?>"><i class="bi bi-receipt me-2"></i> Ventas del Día</a></li>
            
            <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
            
            <li><a href="?controlador=usuario&accion=cerrarSesion" class="text-danger"><i class="bi bi-box-arrow-right me-2" style="color:#dc3545;"></i> Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <button class="btn-menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold" style="color: #cbd5e1;"><?php echo $_SESSION['usuario']['nombre']; ?></span>
                <span class="badge bg-success"><?php echo $_SESSION['usuario']['nombreSede']; ?></span>
            </div>
        </div>

        <div id="idForm">
            <?php include_once("ruteador.php"); ?>
        </div>
    </div>

    <script src="../../Resources/sweetalert/js/sweetalert2.js"></script>
    <script src="../../Resources/js/jquery-latest.js"></script>
    <script src="../../Resources/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload(); 
            }
        });
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('overlay').classList.toggle('show');
        }
    </script>
</body>
</html>