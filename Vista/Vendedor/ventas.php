<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$busqueda = $_GET['buscar'] ?? '';
$idModopago = $_GET['modopago'] ?? '';
?>

<style>
    .card-custom { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
    .tabla-custom thead th { background: #334155; color: #f8fafc; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border: none; padding: 15px; }
    .tabla-custom tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #e2e8f0; color: #334155; font-weight: 500; }
    .tabla-custom tbody tr:hover { background-color: #f8fafc; }
    
    .btn-action { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: none; color: #fff; font-size: 1.1rem; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-ver { background: #3b82f6; } .btn-ver:hover { background: #2563eb; transform: translateY(-2px); }

    @media (max-width: 800px) {
        .tabla-custom thead { display: none; }
        .tabla-custom tbody tr { display: block; margin-bottom: 15px; background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 10px; border: 1px solid #e2e8f0; }
        .tabla-custom tbody td { display: flex; justify-content: space-between; align-items: center; border: none; padding: 12px 10px; border-bottom: 1px dashed #e2e8f0; text-align: right; }
        .tabla-custom tbody td:last-child { border-bottom: none; justify-content: center; padding-top: 15px; gap: 10px; }
        .tabla-custom tbody td::before { content: attr(data-label); font-weight: 700; color: #64748b; font-size: 0.85rem; text-transform: uppercase; text-align: left; }
    }
</style>

<!-- Encabezado -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0" style="color:#0f172a;"><i class="bi bi-receipt me-2" style="color:#10b981;"></i> Ventas del Día</h3>
        <p class="text-muted mb-0">Historial de ventas realizadas en tu sede.</p>
    </div>
    <div class="col-md-6 text-end">
        <div class="card card-custom d-inline-block px-4 py-2">
            <span class="text-muted" style="font-size: 0.85rem;">Total Recaudado (Efectivo/Transferencia)</span>
            <h3 class="fw-bold mb-0 text-success">$<?php echo number_format($totalDia, 0); ?></h3>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="controlador" value="venta">
            <input type="hidden" name="accion" value="inicio">
            <input type="hidden" name="dive" value="l7">
            
            <div class="col-md-3">
                <label class="fw-bold text-muted" style="font-size: 0.8rem;">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?php echo $fecha; ?>">
            </div>
            <div class="col-md-4">
                <label class="fw-bold text-muted" style="font-size: 0.8rem;">Buscar (Cliente o # Venta)</label>
                <input type="text" name="buscar" class="form-control" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Ej: Juan o 00015">
            </div>
            <div class="col-md-3">
                <label class="fw-bold text-muted" style="font-size: 0.8rem;">Medio de Pago</label>
                <select name="modopago" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($modopagos as $mp): ?>
                        <option value="<?php echo $mp['idModopago']; ?>" <?php echo ($idModopago == $mp['idModopago']) ? 'selected' : ''; ?>>
                            <?php echo $mp['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 fw-bold" type="submit">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card card-custom" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="table-responsive">
        <table class="table tabla-custom mb-0">
            <thead>
                <tr>
                    <th># Venta</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Método Pago</th>
                    <th>Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ventas)): ?>
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay ventas registradas con estos filtros.</td></tr>
            <?php else: foreach ($ventas as $v): ?>
                <tr>
                    <td data-label="#" class="fw-bold" style="color: #64748b;">#<?php echo str_pad($v['idVenta'], 5, "0", STR_PAD_LEFT); ?></td>
                    <td data-label="Hora" style="color: #475569;"><?php echo date('h:i A', strtotime($v['fecha'])); ?></td>
                    <td data-label="Cliente" class="fw-bold" style="color: #0f172a;"><?php echo htmlspecialchars($v['cliente']); ?></td>
                    <td data-label="Método Pago">
                        <?php if ($v['modopago'] == 2): ?>
                            <span class="badge" style="background: #fef3c7; color: #d97706; padding: 6px 12px;">Crédito</span>
                        <?php else: ?>
                            <span class="badge" style="background: #e0e7ff; color: #4338ca; padding: 6px 12px;"><?php echo htmlspecialchars($v['metodo_pago']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Total" class="fw-bold" style="font-size: 1.1rem; color: #10b981;">$<?php echo number_format($v['total'], 0); ?></td>
                    <td class="text-center text-md-end">
                        <button class="btn btn-action btn-ver" title="Ver Detalle" onclick="verDetalleVenta(<?php echo $v['idVenta']; ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginador -->
<?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div class="card card-custom mt-3 border-0" style="background: #f8fafc;">
        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-muted" style="font-size: 0.9rem; font-weight: 500;">
                Mostrando <span class="fw-bold" style="color: #0f172a;"><?php echo $inicio + 1; ?></span> 
                a <span class="fw-bold" style="color: #0f172a;"><?php echo min($inicio + $porPagina, $totalRegistros); ?></span> 
                de <span class="fw-bold" style="color: #0f172a;"><?php echo $totalRegistros; ?></span> ventas
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 flex-wrap" style="gap: 5px; justify-content: center;">
                    <?php 
                    // Variables para mantener los filtros en los enlaces del paginador
                    $queryParams = "&fecha=" . urlencode($fecha) . "&buscar=" . urlencode($busqueda) . "&modopago=" . urlencode($idModopago);
                    
                    $rango = 2;
                    $inicio_rango = max(1, $pagina - $rango);
                    $fin_rango = min($totalPaginas, $pagina + $rango);
                    ?>

                    <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=venta&accion=inicio&dive=l7&pagina=<?php echo $pagina - 1; ?><?php echo $queryParams; ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"><i class="bi bi-chevron-left"></i></a>
                    </li>

                    <?php if ($inicio_rango > 1): ?>
                        <li class="page-item"><a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=venta&accion=inicio&dive=l7&pagina=1<?php echo $queryParams; ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">1</a></li>
                        <?php if ($inicio_rango > 2): ?><li class="page-item disabled"><span class="page-link border-0" style="background: transparent; color: #94a3b8;">...</span></li><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $inicio_rango; $i <= $fin_rango; $i++): ?>
                        <li class="page-item">
                            <a class="page-link border-0 d-flex align-items-center justify-content-center <?php echo ($pagina == $i) ? 'fw-bold text-white' : ''; ?>" href="?controlador=venta&accion=inicio&dive=l7&pagina=<?php echo $i; ?><?php echo $queryParams; ?>" style="<?php echo ($pagina == $i) ? 'background: #3b82f6; box-shadow: 0 4px 10px rgba(59,130,246,0.3);' : 'color: #475569; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'; ?> width: 36px; height: 36px; border-radius: 10px;"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($fin_rango < $totalPaginas): ?>
                        <?php if ($fin_rango < $totalPaginas - 1): ?><li class="page-item disabled"><span class="page-link border-0" style="background: transparent; color: #94a3b8;">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=venta&accion=inicio&dive=l7&pagina=<?php echo $totalPaginas; ?><?php echo $queryParams; ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"><?php echo $totalPaginas; ?></a></li>
                    <?php endif; ?>

                    <li class="page-item <?php echo ($pagina >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=venta&accion=inicio&dive=l7&pagina=<?php echo $pagina + 1; ?><?php echo $queryParams; ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>

<script>
    function verDetalleVenta(idVenta) {
        Swal.fire({
            title: 'Cargando detalle...',
            allowOutsideClick: false,
            onBeforeOpen: () => { Swal.showLoading(); }
        });

        fetch(`?controlador=venta&accion=verDetalle&dive=l7&idVenta=${idVenta}`)
            .then(r => r.text()) // Leemos como texto primero para evitar errores de PHP
            .then(text => {
                try {
                    const detalles = JSON.parse(text);
                    let html = `
                        <style>
                            .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                            .swal2-title { color: #f8fafc !important; }
                            .tabla-detalle { width: 100%; color: #cbd5e1; text-align: left; margin-top: 15px; border-collapse: collapse; }
                            .tabla-detalle th { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; padding: 10px; border-bottom: 1px solid #334155; }
                            .tabla-detalle td { padding: 10px; border-bottom: 1px solid #334155; font-size: 0.9rem; }
                        </style>
                        <table class="tabla-detalle">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Cant.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    detalles.forEach(d => {
                        html += `
                            <tr>
                                <td><b>${d.producto}</b></td>
                                <td>${d.codigo_lote}</td>
                                <td>${d.cantidad}</td>
                                <td>$${new Intl.NumberFormat('es-CO').format(d.subtotal)}</td>
                            </tr>
                        `;
                    });

                    html += `</tbody></table>`;
                    Swal.fire({
                        title: `Detalle Venta #${idVenta}`,
                        html: html,
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                } catch (e) {
                    console.error("Error de PHP:", text);
                    Swal.fire({type: 'error', title: 'Error', text: 'No se pudo leer el detalle de la venta.'});
                }
            });
    }
</script>