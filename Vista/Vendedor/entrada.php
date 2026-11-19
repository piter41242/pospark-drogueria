<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #0f172a;">Bienvenido, <?php echo $_SESSION['usuario']['nombre']; ?></h2>
        <p style="color: #475569; font-weight: 500;">Aquí está el resumen de tu jornada en <?php echo $_SESSION['usuario']['nombreSede']; ?>.</p>
    </div>
</div>

<!-- Tarjetas de Acceso Rápido -->
<div class="row mb-5">
    <!-- POS -->
    <div class="col-md-6 mb-4">
        <a href="?controlador=carrito&accion=inicio&dive=l3" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #20c997 0%, #198754 100%); border-radius: 24px; padding: 40px 20px; text-align: center; color: white; box-shadow: 0 10px 20px rgba(25, 135, 84, 0.4); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-cart-plus" style="font-size: 4rem; margin-bottom: 15px;"></i>
                <h4 style="font-weight: 800; margin: 0;">Nueva Venta (POS)</h4>
            </div>
        </a>
    </div>

    <!-- Carritos Abiertos -->
    <div class="col-md-6 mb-4">
        <a href="?controlador=carrito&accion=abiertos&dive=l4" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #ffda6a 0%, #ffc107 100%); border-radius: 24px; padding: 40px 20px; text-align: center; color: #212529; box-shadow: 0 10px 20px rgba(255, 193, 7, 0.4); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-8px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-hourglass-split" style="font-size: 4rem; margin-bottom: 15px;"></i>
                <h4 style="font-weight: 800; margin: 0;">Carritos Abiertos</h4>
            </div>
        </a>
    </div>
</div>

<!-- Alertas de Inventario (Solo lectura) -->
<div class="row">
    <div class="col-md-12">
        <div style="background: #1e293b; border-radius: 16px; padding: 20px; color: #f8fafc;">
            <h5 style="color: #fbbf24; font-weight: 700;"><i class="bi bi-exclamation-triangle-fill"></i> Alerta de Stock</h5>
            <p style="color: #cbd5e1; margin: 0;">Recuerda revisar con el administrador si hay productos con stock bajo antes de iniciar tus ventas.</p>
        </div>
    </div>
</div>