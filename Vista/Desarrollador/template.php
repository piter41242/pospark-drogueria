<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Droguería | Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../../Resources/bootstrap/css/bootstrap.min.css">
    <!-- Iconos -->
    <link rel="stylesheet" href="../../Resources/css/fonts.css">
    <link rel="stylesheet" href="../../Resources/sweetalert/css/sweetalert2.css">
    <link rel="stylesheet" href="../../Resources/css/menu.css">
    
    <style>
        
    </style>
</head>
<body>

    <!-- OVERLAY para cerrar el menú en móviles -->
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <!-- SIDEBAR (MENÚ LATERAL) -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <!-- Icono de Farmacia -->
            <i class="fas fa-prescription-bottle-alt" style="font-size: 2.5rem; color: #198754;"></i>
            <h4>Droguería</h4>
            <small class="text-muted"><?php echo $_SESSION['usuario']['nombreSede']; ?></small>
        </div>
        <ul>
            <li><a href="?controlador=usuario&accion=empresas&dive=l2" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l2')?'active':''; ?>"><i class="bi bi-building me-2"></i> Empresas</a></li>
                
            <li><a href="?controlador=pagos&accion=inicio&dive=l3" class="<?php echo (isset($_GET['dive']) && $_GET['dive']=='l3')?'active':''; ?>"><i class="bi bi-cash-coin me-2"></i> Pagos y Reportes</a></li>
                
            <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
                
            <li><a href="?controlador=usuario&accion=cerrarSesion" class="text-danger"><i class="bi bi-box-arrow-right me-2" style="color:#dc3545;"></i> Cerrar sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <button class="btn-menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold text-success"><?php echo $_SESSION['usuario']['nombre']; ?></span>
                <span class="badge bg-success"><?php echo $_SESSION['usuario']['nombreSede']; ?></span>
            </div>
        </div>

        <!-- AQUÍ SE CARGA LA VISTA DINÁMICA -->
        <div id="idForm">
            <?php include_once("ruteador.php"); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../Resources/sweetalert/js/sweetalert2.js"></script>
    <script src="../../Resources/js/jquery-latest.js"></script>
    <script src="../../Resources/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para abrir/cerrar el menú en móviles
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