<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: #146c43;">Panel de Administración</h2>
        <p style="color: #475569; font-weight: 500;">Bienvenido de nuevo, aquí tienes el resumen de hoy.</p>
    </div>
</div>

<!-- TARJETAS DE ACCESO RÁPIDO (CON ICONOS PROFESIONALES) -->
<div class="row mb-5">
    <!-- Tarjeta 1 (Verde) -->
    <div class="col-md-4 mb-4">
        <a href="?controlador=carrito&accion=inicio&dive=l3" style="text-decoration: none;">
            <div style="
                background: linear-gradient(135deg, #20c997 0%, #198754 100%);
                border-radius: 24px; padding: 40px 20px; text-align: center; color: white; 
                box-shadow: 0 10px 20px rgba(25, 135, 84, 0.4), inset 0 2px 0 rgba(255,255,255,0.4);
                border: 1px solid rgba(25, 135, 84, 0.5); transition: transform 0.3s, box-shadow 0.3s;
            " onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 30px rgba(25, 135, 84, 0.6), inset 0 2px 0 rgba(255,255,255,0.4)';" 
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 10px 20px rgba(25, 135, 84, 0.4), inset 0 2px 0 rgba(255,255,255,0.4)';">
                
                <i class="bi bi-cart-plus" style="font-size: 4.5rem; margin-bottom: 15px; display: inline-block; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));"></i>
                <h4 style="font-weight: 800; margin: 0; letter-spacing: 1px;">Nueva Venta (POS)</h4>
            </div>
        </a>
    </div>

    <!-- Tarjeta 2 (Azul) -->
    <div class="col-md-4 mb-4">
        <a href="?controlador=usuario&accion=usuario&dive=l7" style="text-decoration: none;">
            <div style="
                background: linear-gradient(135deg, #3d8bfd 0%, #0d6efd 100%);
                border-radius: 24px; padding: 40px 20px; text-align: center; color: white; 
                box-shadow: 0 10px 20px rgba(13, 110, 253, 0.4), inset 0 2px 0 rgba(255,255,255,0.4);
                border: 1px solid rgba(13, 110, 253, 0.5); transition: transform 0.3s, box-shadow 0.3s;
            " onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 30px rgba(13, 110, 253, 0.6), inset 0 2px 0 rgba(255,255,255,0.4)';" 
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 10px 20px rgba(13, 110, 253, 0.4), inset 0 2px 0 rgba(255,255,255,0.4)';">
                
                <i class="bi bi-people-fill" style="font-size: 4.5rem; margin-bottom: 15px; display: inline-block; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));"></i>
                <h4 style="font-weight: 800; margin: 0; letter-spacing: 1px;">Gestionar Usuarios</h4>
            </div>
        </a>
    </div>

    <!-- Tarjeta 3 (Amarilla) -->
    <div class="col-md-4 mb-4">
        <a href="?controlador=inventario&accion=inicio&dive=l5" style="text-decoration: none;">
            <div style="
                background: linear-gradient(135deg, #ffda6a 0%, #ffc107 100%);
                border-radius: 24px; padding: 40px 20px; text-align: center; color: #212529; 
                box-shadow: 0 10px 20px rgba(255, 193, 7, 0.4), inset 0 2px 0 rgba(255,255,255,0.6);
                border: 1px solid rgba(255, 193, 7, 0.5); transition: transform 0.3s, box-shadow 0.3s;
            " onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 30px rgba(255, 193, 7, 0.6), inset 0 2px 0 rgba(255,255,255,0.6)';" 
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 10px 20px rgba(255, 193, 7, 0.4), inset 0 2px 0 rgba(255,255,255,0.6)';">
                
                <i class="bi bi-box-seam" style="font-size: 4.5rem; margin-bottom: 15px; display: inline-block; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));"></i>
                <h4 style="font-weight: 800; margin: 0; letter-spacing: 1px;">Inventario</h4>
            </div>
        </a>
    </div>
</div>
<!-- ALERTAS EN MODO OSCURO -->
<div class="row">
    <div class="col-md-6 mb-4">
        <!-- Tarjeta Oscura Stock Bajo -->
        <div style="background: #1e293b; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); overflow: hidden; border: 1px solid #334155;">
            <div style="padding: 20px; border-bottom: 1px solid #334155; background: #0f172a;">
                <h5 style="margin: 0; font-weight: 700; color: #f87171; font-size: 1.1rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Stock Bajo
                </h5>
            </div>
            <div style="padding: 20px;">
                <?php if (count($stockBajo) == 0): ?>
                    <p style="color: #4ade80; font-weight: 500; margin: 0;">Todo el inventario está por encima del mínimo.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($stockBajo as $p): ?>
                            <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #334155; color: #cbd5e1; font-weight: 500;">
                                <span><?php echo htmlspecialchars($p['nombre']); ?></span>
                                <span style="background: #dc3545; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem;"><?php echo $p['stock_disponible']; ?> uds</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <!-- Tarjeta Oscura Lotes por vencer -->
        <div style="background: #1e293b; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); overflow: hidden; border: 1px solid #334155;">
            <div style="padding: 20px; border-bottom: 1px solid #334155; background: #0f172a;">
                <h5 style="margin: 0; font-weight: 700; color: #fbbf24; font-size: 1.1rem;">
                    <i class="bi bi-calendar-x-fill"></i> Lotes por vencer (90 días)
                </h5>
            </div>
            <div style="padding: 20px;">
                <?php if (count($porVencer) == 0): ?>
                    <p style="color: #4ade80; font-weight: 500; margin: 0;">No hay lotes próximos a vencer.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($porVencer as $l): ?>
                            <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #334155; color: #cbd5e1; font-weight: 500;">
                                <span><?php echo htmlspecialchars($l['producto']); ?> <br><small style="color: #64748b;">Lote: <?php echo $l['codigo_lote']; ?></small></span>
                                <span style="background: #ffc107; color: #212529; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;"><?php echo $l['dias_restantes']; ?> días</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>