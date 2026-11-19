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

        // Totales globales
        $totalUsuarios = $bd->query("SELECT COUNT(*) as t FROM usuario")->fetch()['t'];
        $totalSedes    = $bd->query("SELECT COUNT(*) as t FROM sede WHERE estado=1")->fetch()['t'];
        $totalProductos= $bd->query("SELECT COUNT(*) as t FROM producto WHERE estado=1")->fetch()['t'];
        
        // Ventas del día (global)
        $ventasHoy = $bd->query("SELECT SUM(total) as t, COUNT(*) as c FROM venta WHERE DATE(fecha)=CURDATE()")->fetch();
        ?>

        <div class="row mb-4">
            <div class="col-sm-12">
                <h1>Panel del Desarrollador</h1>
                <p class="text-muted">
                    Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></strong>
                    &nbsp;|&nbsp; Acceso Total al Sistema
                </p>
                <hr>
            </div>
        </div>

        <!-- TARJETAS DE RESUMEN -->
        <div class="row mb-4 text-center">
            <div class="col-sm-3">
                <div class="card bg-primary text-white p-3">
                    <h2><?php echo $totalUsuarios; ?></h2>
                    <p>Usuarios Totales</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card bg-success text-white p-3">
                    <h2><?php echo $totalSedes; ?></h2>
                    <p>Sedes Activas</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card bg-warning text-dark p-3">
                    <h2><?php echo $totalProductos; ?></h2>
                    <p>Productos</p>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card bg-info text-white p-3">
                    <h2>$<?php echo number_format($ventasHoy['t'] ?? 0, 0, ',', '.'); ?></h2>
                    <p>Ventas Hoy (<?php echo $ventasHoy['c'] ?? 0; ?>)</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-12">
                <h3>Accesos Rápidos</h3>
                <a href="?controlador=usuario&accion=usuario&dive=l3" class="btn btn-primary btn-lg m-1">👥 Gestionar Usuarios</a>
                <a href="?controlador=carrito&accion=inicio&dive=l6" class="btn btn-success btn-lg m-1">🛒 Ir al POS</a>
                <a href="?controlador=inventario&accion=inicio&dive=l7" class="btn btn-warning btn-lg m-1">📦 Inventario</a>
            </div>
        </div>

        <!-- LISTADO DE SEDES -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <h3>🏢 Sedes del Sistema</h3>
                <?php
                $sedes = $bd->query("SELECT * FROM sede")->fetchAll();
                ?>
                <table class="table table-sm table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Ciudad</th>
                            <th>Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sedes as $s): ?>
                        <tr>
                            <td><?php echo $s['idSede']; ?></td>
                            <td><?php echo htmlspecialchars($s['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($s['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($s['ciudad']); ?></td>
                            <td><?php echo htmlspecialchars($s['telefono']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="col-sm-1"></div>
</div>