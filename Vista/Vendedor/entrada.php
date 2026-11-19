<?php
if (!$_SESSION) { header("Location:../../index.php"); exit(); }
?>
<link rel="stylesheet" href="../../Resources/css/entradaVendedor.css">
<link rel="stylesheet" href="../../Resources/css/tablalarga.css">

<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">

        <?php
        include_once("../../Modelo/conexion.php");
        $bd = BD::crearInstancia();

        // Stock bajo en la sede
        $sql = $bd->prepare("SELECT * FROM v_stock WHERE idSede=? AND alerta=1 ORDER BY stock_disponible ASC LIMIT 10");
        $sql->execute(array($_SESSION['usuario']['idSede']));
        $stockBajo = $sql->fetchAll();

        // Lotes por vencer (90 días)
        $sql = $bd->prepare("
            SELECT l.*, p.nombre AS producto, s.nombre AS sede
            FROM lote l
            JOIN producto p ON p.idProducto=l.idProducto
            JOIN sede s ON s.idSede=l.idSede
            WHERE l.idSede=? AND l.estado=1 AND l.cantidad_actual>0
              AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            ORDER BY l.fecha_vencimiento ASC LIMIT 10
        ");
        $sql->execute(array($_SESSION['usuario']['idSede']));
        $porVencer = $sql->fetchAll();

        // Carritos abiertos del usuario
        $sql = $bd->prepare("
            SELECT c.*, cl.nombre AS cliente
            FROM carrito c
            JOIN cliente cl ON cl.idCliente=c.idCliente
            WHERE c.idSede=? AND c.usuario_apertura=? AND c.estado IN ('ABIERTO','EN_RUTA')
            ORDER BY c.fecha_apertura DESC
        ");
        $sql->execute(array($_SESSION['usuario']['idSede'], $_SESSION['usuario']['idUsuario']));
        $carritos = $sql->fetchAll();
        ?>

        <div class="row mb-4">
            <div class="col-sm-12">
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></h1>
                <p class="text-muted">
                    Sede: <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombreSede']); ?></strong>
                    &nbsp;|&nbsp; Rol: <strong>Vendedor</strong>
                </p>
                <hr>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-4">
                <a href="?controlador=carrito&accion=inicio&dive=l3" class="btn btn-primary btn-lg w-100" style="height:120px;font-size:22px;">
                    🛒 NUEVA VENTA (POS)
                </a>
            </div>
            <div class="col-sm-4">
                <a href="?controlador=carrito&accion=abiertos&dive=l4" class="btn btn-warning btn-lg w-100" style="height:120px;font-size:22px;">
                    ⏳ CARRITOS ABIERTOS (<?php echo count($carritos); ?>)
                </a>
            </div>
            <div class="col-sm-4">
                <a href="?controlador=inventario&accion=inicio&dive=l5" class="btn btn-success btn-lg w-100" style="height:120px;font-size:22px;">
                    📦 INVENTARIO
                </a>
            </div>
        </div>

        <!-- STOCK BAJO -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <h3>⚠️ Stock bajo</h3>
                <?php if (count($stockBajo) == 0): ?>
                    <p class="text-success">Todo el inventario está por encima del mínimo.</p>
                <?php else: ?>
                    <table class="table table-sm table-bordered">
                        <thead class="table-danger">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stockBajo as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['codigo_interno']); ?></td>
                                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td><span class="badge bg-danger"><?php echo $p['stock_disponible']; ?></span></td>
                                <td><?php echo $p['stock_minimo']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- LOTES POR VENCER -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <h3>📅 Lotes por vencer (90 días)</h3>
                <?php if (count($porVencer) == 0): ?>
                    <p class="text-success">No hay lotes próximos a vencer.</p>
                <?php else: ?>
                    <table class="table table-sm table-bordered">
                        <thead class="table-warning">
                            <tr>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Vence</th>
                                <th>Días</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($porVencer as $l): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($l['codigo_lote']); ?></td>
                                <td><?php echo htmlspecialchars($l['producto']); ?></td>
                                <td><?php echo $l['fecha_vencimiento']; ?></td>
                                <td><span class="badge bg-warning text-dark"><?php echo $l['dias_restantes']; ?> días</span></td>
                                <td><?php echo $l['cantidad_actual']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <div class="col-sm-1"></div>
</div>