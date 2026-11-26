<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>

<style>
    .card-custom { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
    .tabla-custom thead th { background: #334155; color: #f8fafc; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border: none; padding: 15px; }
    .tabla-custom tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #e2e8f0; color: #334155; font-weight: 500; }
    .tabla-custom tbody tr:hover { background-color: #f8fafc; }
    
    .btn-action { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: none; color: #fff; font-size: 1.1rem; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-retomar { background: #10b981; } .btn-retomar:hover { background: #059669; transform: translateY(-2px); }
    .btn-anular { background: #ef4444; } .btn-anular:hover { background: #dc2626; transform: translateY(-2px); }

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
    <div class="col-md-8 col-6">
        <h3 class="fw-bold mb-0" style="color:#0f172a;"><i class="bi bi-hourglass-split me-2" style="color:#f59e0b;"></i> Carritos en Espera</h3>
        <p class="text-muted mb-0 d-none d-md-block">Retoma o anula ventas que quedaron pendientes.</p>
    </div>
</div>

<!-- Tabla -->
<div class="card card-custom" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="table-responsive">
        <table class="table tabla-custom mb-0">
            <thead>
                <tr>
                    <th># Carrito</th>
                    <th>Fecha/Hora</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($carritos)): ?>
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay carritos abiertos.</td></tr>
            <?php else: foreach ($carritos as $c): ?>
                <tr id="fila-carrito-<?php echo $c['idCarrito']; ?>">
                    <td data-label="#" class="fw-bold" style="color: #64748b;">#<?php echo str_pad($c['idCarrito'], 5, "0", STR_PAD_LEFT); ?></td>
                    <td data-label="Fecha" style="color: #475569;"><?php echo date('d/m/Y h:i A', strtotime($c['fecha_apertura'])); ?></td>
                    <td data-label="Cliente" class="fw-bold" style="color: #0f172a;"><?php echo htmlspecialchars($c['cliente']); ?></td>
                    <td data-label="Vendedor" style="color: #475569;"><?php echo htmlspecialchars($c['vendedor']); ?></td>
                    <td data-label="Total" class="fw-bold" style="font-size: 1.1rem; color: #ef4444;">$<?php echo number_format($c['total'], 0); ?></td>
                    <td class="text-center text-md-end">
                        <button class="btn btn-action btn-retomar me-1" title="Retomar Venta" onclick="retomarCarrito(<?php echo $c['idCarrito']; ?>)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <button class="btn btn-action btn-anular" title="Anular Carrito" onclick="anularCarrito(<?php echo $c['idCarrito']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function retomarCarrito(idCarrito) {
        Swal.fire({
            title: '¿Retomar esta venta?',
            html: 'Se cargarán los productos de este carrito en tu POS actual. Si tienes productos en tu carrito actual, se perderán.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sí, retomar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la lógica para asignar el carrito al usuario actual y redirigir al POS
                window.location.href = `?controlador=carrito&accion=retomar&idCarrito=${idCarrito}&dive=l3`;
            }
        });
    }

    function anularCarrito(idCarrito) {
        Swal.fire({
            title: '¿Anular carrito?',
            text: 'Los productos serán devueltos al inventario y el carrito se eliminará.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('idCarrito', idCarrito);

                fetch('?controlador=carrito&accion=anularCarrito&dive=l4', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({type: 'success', title: '¡Anulado!', text: res.msg, timer: 1500, showConfirmButton: false})
                        .then(() => {
                            document.getElementById('fila-carrito-' + idCarrito).remove();
                        });
                    } else {
                        Swal.fire({type: 'error', title: 'Error', text: res.msg});
                    }
                });
            }
        });
    }
</script>