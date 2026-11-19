<?php
if (!isset($_SESSION['usuario']['idUsuario'])) {
    header("Location:../../index.php");
    exit();
}
$busqueda = $_GET['buscar'] ?? '';
$rol_actual = (int)$_SESSION['usuario']['permiso'];
?>

<style>
    .card-custom {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .tabla-custom thead th {
        background: #334155;
        color: #f8fafc;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px;
    }

    .tabla-custom tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        font-weight: 500;
    }

    .tabla-custom tbody tr:hover {
        background-color: #f8fafc;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        color: #fff;
        font-size: 1.1rem;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-adjust {
        background: #10b981;
    }

    .btn-adjust:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    @media (max-width: 800px) {
        .tabla-custom thead {
            display: none;
        }

        .tabla-custom tbody tr {
            display: block;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 10px;
            border: 1px solid #e2e8f0;
        }

        .tabla-custom tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            padding: 12px 10px;
            border-bottom: 1px dashed #e2e8f0;
            text-align: right;
        }

        .tabla-custom tbody td:last-child {
            border-bottom: none;
            justify-content: center;
            padding-top: 15px;
            gap: 10px;
        }

        .tabla-custom tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            text-align: left;
        }
    }
</style>

<!-- Encabezado y Botones -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6 col-12 mb-3 mb-md-0">
        <h3 class="fw-bold mb-0" style="color:#0f172a;"><i class="bi bi-box-seam me-2" style="color:#10b981;"></i> Inventario</h3>
        <p class="text-muted mb-0 d-none d-md-block" style="font-size: 0.95rem;">Control de existencias en tu sede.</p>
    </div>
    <div class="col-md-6 col-12 text-md-end text-start">
        <?php if (in_array($rol_actual, [1, 2, 5])): ?>
            <button onclick="formularioProducto()" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm me-2 mb-2 mb-md-0" style="background: #3b82f6; border:none;">
                <i class="bi bi-box me-1"></i> Nuevo Producto
            </button>
            <button onclick="formularioLote()" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm mb-2 mb-md-0" style="background: #10b981; border:none;">
                <i class="bi bi-box-arrow-in-down me-1"></i> Ingreso Mercancía
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador -->
<div class="card card-custom mb-4" style="background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);">
    <div class="card-body p-2">
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="controlador" value="inventario">
            <input type="hidden" name="accion" value="inicio">
            <input type="hidden" name="dive" value="l6">

            <div class="position-relative flex-grow-1">
                <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem;"></i>
                <input type="text" name="buscar" class="form-control border-0 shadow-none ps-5 py-2"
                    placeholder="Buscar por nombre o código..."
                    value="<?php echo htmlspecialchars($busqueda ?? ''); ?>"
                    style="background: #f1f5f9; border-radius: 12px; font-size: 0.95rem; color: #1e293b;">
            </div>
            <button class="btn btn-primary px-4 py-2 fw-bold" style="background: #3b82f6; border:none; border-radius: 12px;">Buscar</button>

            <?php if (!empty($busqueda)): ?>
                <a href="?controlador=inventario&accion=inicio&dive=l6" class="btn btn-light px-3 py-2 fw-bold text-muted" style="border-radius: 12px; border: 1px solid #e2e8f0;" title="Limpiar búsqueda">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card card-custom" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="table-responsive">
        <table class="table tabla-custom mb-0" id="tablaInventario" style="border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border-radius: 16px 0 0 0;">Código</th>
                    <th>Producto</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                    <th class="text-center" style="border-radius: 0 16px 0 0;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay productos en el inventario.</td>
                    </tr>
                    <?php else: foreach ($productos as $p): ?>
                        <tr id="fila-prod-<?php echo $p['idProducto']; ?>">
                            <td data-label="Código" class="fw-bold" style="color: #64748b;"><?php echo htmlspecialchars($p['codigo_interno']); ?></td>
                            <td data-label="Producto" class="fw-bold" style="color: #0f172a;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td data-label="Stock Actual" class="fw-bold" style="font-size: 1.1rem; color: <?php echo $p['alerta'] ? '#ef4444' : '#10b981'; ?>;">
                                <?php echo $p['stock_disponible']; ?>
                            </td>
                            <td data-label="Stock Mínimo" style="color: #475569;"><?php echo $p['stock_minimo']; ?></td>
                            <td data-label="Estado">
                                <?php if ($p['alerta'] == 1): ?>
                                    <span class="badge rounded-pill" style="background: #fee2e2; color: #b91c1c; padding: 6px 12px;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Bajo</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #047857; padding: 6px 12px;"><i class="bi bi-check-circle-fill me-1"></i>Óptimo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-md-end">
                                <!-- Solo Admin y Superadmin pueden ajustar -->
                                <?php if (in_array($rol_actual, [1, 2, 5])): ?>
                                    <button class="btn btn-action btn-adjust" title="Ajustar Inventario"
                                        onclick="abrirAjuste(<?php echo $p['idProducto']; ?>, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>', <?php echo $p['stock_disponible']; ?>)">
                                        <i class="bi bi-plus-slash-minus"></i>
                                    </button>
                                    <a class="btn btn-action me-1" style="background: #3b82f6;" title="Ver Lotes"
                                        onclick="verLotes(<?php echo $p['idProducto']; ?>, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-layers"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginador Estilizado e Inteligente -->
<?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div class="card card-custom mt-3 border-0" style="background: #f8fafc;">
        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

            <div class="text-muted" style="font-size: 0.9rem; font-weight: 500;">
                Mostrando <span class="fw-bold" style="color: #0f172a;"><?php echo $inicio + 1; ?></span>
                a <span class="fw-bold" style="color: #0f172a;"><?php echo min($inicio + $porPagina, $totalRegistros); ?></span>
                de <span class="fw-bold" style="color: #0f172a;"><?php echo $totalRegistros; ?></span> productos
            </div>

            <nav>
                <ul class="pagination pagination-sm mb-0 flex-wrap" style="gap: 5px; justify-content: center;">

                    <!-- Botón Anterior -->
                    <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=inventario&accion=inicio&dive=l6&pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($busqueda); ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    // Lógica para mostrar solo un rango de páginas
                    $rango = 2; // Cuántas páginas mostrar a los lados de la actual
                    $inicio_rango = max(1, $pagina - $rango);
                    $fin_rango = min($totalPaginas, $pagina + $rango);

                    // Si estamos lejos del inicio, mostramos la 1 y puntos suspensivos
                    if ($inicio_rango > 1) {
                        echo '<li class="page-item"><a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=inventario&accion=inicio&dive=l6&pagina=1&buscar=' . urlencode($busqueda) . '" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">1</a></li>';
                        if ($inicio_rango > 2) {
                            echo '<li class="page-item disabled"><span class="page-link border-0 d-flex align-items-center justify-content-center" style="background: transparent; color: #94a3b8; width: 36px; height: 36px;">...</span></li>';
                        }
                    }

                    // Imprimimos el rango central
                    for ($i = $inicio_rango; $i <= $fin_rango; $i++): ?>
                        <li class="page-item">
                            <a class="page-link border-0 d-flex align-items-center justify-content-center <?php echo ($pagina == $i) ? 'fw-bold text-white' : ''; ?>"
                                href="?controlador=inventario&accion=inicio&dive=l6&pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>"
                                style="<?php echo ($pagina == $i) ? 'background: #3b82f6; box-shadow: 0 4px 10px rgba(59,130,246,0.3);' : 'color: #475569; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'; ?> width: 36px; height: 36px; border-radius: 10px;">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor;

                    // Si estamos lejos del final, mostramos puntos suspensivos y la última página
                    if ($fin_rango < $totalPaginas) {
                        if ($fin_rango < $totalPaginas - 1) {
                            echo '<li class="page-item disabled"><span class="page-link border-0 d-flex align-items-center justify-content-center" style="background: transparent; color: #94a3b8; width: 36px; height: 36px;">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=inventario&accion=inicio&dive=l6&pagina=' . $totalPaginas . '&buscar=' . urlencode($busqueda) . '" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">' . $totalPaginas . '</a></li>';
                    }
                    ?>

                    <!-- Botón Siguiente -->
                    <li class="page-item <?php echo ($pagina >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=inventario&accion=inicio&dive=l6&pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($busqueda); ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>


<script>
    // Pasamos los datos de PHP a JS para los formularios
    const categoriasOptions = `<?php foreach ($categorias as $c) echo "<option value='{$c['idCategoria']}'>{$c['nombre']}</option>"; ?>`;
    // En tu bloque <script>, cambia la línea de productosOptions por esta:
    const presentacionesOptions = `<?php echo json_encode(array_map(function ($p) {
                                        return "<option value='{$p['idPresentacion']}'>{$p['nombre']}</option>";
                                    }, $presentaciones)); ?>`.replace(/\[|\]|"/g, '');
    const laboratoriosOptions = `<?php foreach ($laboratorios as $l) echo "<option value='{$l['idLaboratorio']}'>{$l['nombre']}</option>"; ?>`;
    const impuestosOptions = `<?php foreach ($impuestos as $i) echo "<option value='{$i['idImpuesto']}'>{$i['nombre']}</option>"; ?>`;
    const productosOptions = `<?php foreach ($todosProductos as $p) echo "<option value='{$p['idProducto']}'>{$p['nombre']} ({$p['codigo_interno']})</option>"; ?>`;
    // Pasamos los datos directo a JS asegurando que no haya problemas de comillas
    const todosLosProductos = <?php echo json_encode($todosProductos ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function formularioProducto() {
        Swal.fire({
            title: 'Nuevo Producto',
            html: `
                <style>
                    .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                    .swal2-title { color: #f8fafc !important; }
                    .swal-premium-form { text-align: left; margin-top: 15px; }
                    .swal-premium-form label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
                    .swal-premium-form .form-control, .swal-premium-form .form-select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 8px 12px !important; font-size: 0.9rem; }
                </style>
                <form id="formProd" class="swal-premium-form">
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label>Código Interno *</label><input type="text" id="prod-codigo" class="form-control" required></div>
                        <div class="col-6"><label>Nombre *</label><input type="text" id="prod-nombre" class="form-control" required></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label>Precio Compra</label><input type="number" id="prod-compra" class="form-control" value="0" step="0.01"></div>
                        <div class="col-6"><label>Precio Venta</label><input type="number" id="prod-venta" class="form-control" value="0" step="0.01"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label>Categoría</label><select id="prod-categoria" class="form-select">${categoriasOptions}</select></div>
                        <div class="col-6"><label>Presentación</label><select id="prod-presentacion" class="form-select">${presentacionesOptions}</select></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label>Laboratorio</label><select id="prod-laboratorio" class="form-select">${laboratoriosOptions}</select></div>
                        <div class="col-6"><label>Impuesto</label><select id="prod-impuesto" class="form-select">${impuestosOptions}</select></div>
                    </div>
                    <div class="mb-2"><label>Stock Mínimo</label><input type="number" id="prod-stockmin" class="form-control" value="5"></div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#475569',
            preConfirm: () => {
                const data = new FormData();
                data.append('codigo_interno', document.getElementById('prod-codigo').value);
                data.append('nombre', document.getElementById('prod-nombre').value);
                data.append('valorCompra', document.getElementById('prod-compra').value);
                data.append('valorVenta', document.getElementById('prod-venta').value);
                data.append('idCategoria', document.getElementById('prod-categoria').value);
                data.append('idPresentacion', document.getElementById('prod-presentacion').value);
                data.append('idLaboratorio', document.getElementById('prod-laboratorio').value);
                data.append('idImpuesto', document.getElementById('prod-impuesto').value);
                data.append('stock_minimo', document.getElementById('prod-stockmin').value);
                return fetch('?controlador=inventario&accion=crearProducto&dive=l6', {
                    method: 'POST',
                    body: data
                }).then(r => r.json());
            }
        }).then(res => {
            if (res.value.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: '¡Listo!',
                    text: res.value.msg,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else if (res.value) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: res.value.msg
                });
            }
        });
    }

    function formularioLote() {
        Swal.fire({
            title: 'Ingreso de Mercancía',
            html: `
                <style>
                    .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                    .swal2-title { color: #f8fafc !important; }
                    .swal-premium-form { text-align: left; margin-top: 15px; }
                    .swal-premium-form label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
                    .swal-premium-form .form-control { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 8px 12px !important; font-size: 0.9rem; }
                    
                    /* Estilos para la lista de resultados */
                    .resultados-busqueda { position: absolute; width: 94%; max-height: 200px; overflow-y: auto; background: #1e293b; border: 1px solid #334155; border-radius: 10px; z-index: 9999; display: none; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
                    .item-resultado { padding: 10px 12px; border-bottom: 1px solid #334155; cursor: pointer; color: #cbd5e1; font-size: 0.9rem; }
                    .item-resultado:hover { background: #10b981; color: white; }
                    .producto-seleccionado { background: #10b98120; border: 1px solid #10b981; color: #10b981; padding: 10px; border-radius: 10px; font-weight: bold; margin-bottom: 10px; display: none; }
                </style>
                <form id="formLote" class="swal-premium-form">
                    <div class="mb-2 position-relative" id="contenedor-buscador">
                        <label for="lote-input-buscar">Buscar Producto (Escribe nombre o código) *</label>
                        <input type="text" id="lote-input-buscar" class="form-control" placeholder="Escribe ej: Dolex..." autocomplete="off">
                        <input type="hidden" id="lote-id-producto">
                        <div id="lote-lista-resultados" class="resultados-busqueda"></div>
                    </div>
                    
                    <div id="lote-seleccionado" class="producto-seleccionado">
                        <i class="bi bi-check-circle-fill"></i> <span id="lote-nombre-seleccionado"></span>
                        <a href="#" onclick="cancelarSeleccionLote(); return false;" style="float: right; color: #ef4444; text-decoration: none;"><i class="bi bi-x-circle"></i></a>
                    </div>

                    <div class="row g-2 mb-2 mt-2">
                        <div class="col-6"><label for="lote-codigo">Lote *</label><input type="text" id="lote-codigo" class="form-control" required></div>
                        <div class="col-6"><label for="lote-vencimiento">Vencimiento *</label><input type="date" id="lote-vencimiento" class="form-control" required></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label for="lote-cantidad">Cantidad *</label><input type="number" id="lote-cantidad" class="form-control" value="1" min="1"></div>
                        <div class="col-6"><label for="lote-costo">Costo Unitario</label><input type="number" id="lote-costo" class="form-control" value="0" step="0.01"></div>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ingresar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#475569',
            preConfirm: () => {
                const idProducto = document.getElementById('lote-id-producto').value;
                if (!idProducto) {
                    Swal.showValidationMessage('Debes seleccionar un producto de la lista.');
                    return false;
                }

                const data = new FormData();
                data.append('idProducto', idProducto);
                data.append('codigo_lote', document.getElementById('lote-codigo').value);
                data.append('fecha_vencimiento', document.getElementById('lote-vencimiento').value);
                data.append('cantidad', document.getElementById('lote-cantidad').value);
                data.append('costo_unitario', document.getElementById('lote-costo').value);

                return fetch('?controlador=inventario&accion=ingresarLote&dive=l6', {
                    method: 'POST',
                    body: data
                }).then(r => r.json());
            }
        }).then(res => {
            if (res.value && res.value.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: '¡Listo!',
                    text: res.value.msg,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else if (res.value) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: res.value.msg
                });
            }
        });

        // MAGIA: Enganchamos el evento después de que el modal se abra
        setTimeout(() => {
            const inputBuscar = document.getElementById('lote-input-buscar');
            const listaResultados = document.getElementById('lote-lista-resultados');

            if (!inputBuscar) return;

            inputBuscar.addEventListener('input', function() {
                const texto = this.value;
                if (texto.length < 2) {
                    listaResultados.style.display = 'none';
                    return;
                }

                fetch(`?controlador=inventario&accion=buscarProductosAjax&dive=l6&q=${encodeURIComponent(texto)}`)
                    .then(r => {
                        // Validar que la respuesta sea JSON antes de parsear
                        const contentType = r.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return r.json();
                        } else {
                            throw new Error("El servidor no devolvió un JSON válido");
                        }
                    })
                    .then(productos => {
                        listaResultados.innerHTML = '';
                        if (productos.length > 0) {
                            listaResultados.style.display = 'block';
                            productos.forEach(prod => {
                                const div = document.createElement('div');
                                div.className = 'item-resultado';
                                div.innerHTML = `<b>${prod.nombre}</b> <br><small style="color:#94a3b8">${prod.codigo_interno}</small>`;
                                div.onclick = function() {
                                    document.getElementById('lote-id-producto').value = prod.idProducto;
                                    document.getElementById('lote-nombre-seleccionado').innerText = `${prod.nombre} (${prod.codigo_interno})`;
                                    document.getElementById('lote-seleccionado').style.display = 'block';
                                    document.getElementById('contenedor-buscador').style.display = 'none';
                                };
                                listaResultados.appendChild(div);
                            });
                        } else {
                            listaResultados.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error("Error en la búsqueda:", err);
                        listaResultados.style.display = 'none';
                    });
            });
        }, 300); // Esperamos 300ms para asegurar que el HTML del modal ya se pintó
    }

    function cancelarSeleccionLote() {
        document.getElementById('lote-id-producto').value = '';
        document.getElementById('lote-seleccionado').style.display = 'none';
        document.getElementById('contenedor-buscador').style.display = 'block';
        document.getElementById('lote-input-buscar').value = '';
        document.getElementById('lote-input-buscar').focus();
    }

    // Función para cancelar la selección
    function cancelarSeleccion() {
        document.getElementById('lote-id-producto').value = '';
        document.getElementById('lote-seleccionado').style.display = 'none';
        document.getElementById('contenedor-buscador').style.display = 'block';
        document.getElementById('lote-input-buscar').value = '';
        document.getElementById('lote-input-buscar').focus();
    }

    function abrirAjuste(idProducto, nombre, stockActual) {
        Swal.fire({
            title: 'Ajustar Inventario',
            html: `
                <style>
                    .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                    .swal2-title { color: #f8fafc !important; }
                    .swal-premium-form { text-align: left; margin-top: 15px; }
                    .swal-premium-form label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
                    .swal-premium-form .form-control, .swal-premium-form .form-select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 8px 12px !important; font-size: 0.9rem; }
                </style>
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="background: #10b98120; width: 70px; height: 70px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-plus-slash-minus" style="color:#10b981; font-size: 2rem;"></i>
                    </div>
                    <p style="color: #cbd5e1; margin-top: 10px; font-size: 0.9rem;">Producto: <b>${nombre}</b><br>Stock Actual: <b>${stockActual}</b></p>
                </div>
                <form id="formAjuste" class="swal-premium-form">
                    <div class="mb-2">
                        <label>Tipo de Ajuste *</label>
                        <select id="ajuste-tipo" class="form-select">
                            <option value="ENTRADA">Entrada (Sumar al stock)</option>
                            <option value="SALIDA">Salida (Restar del stock)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Cantidad *</label>
                        <input type="number" id="ajuste-cantidad" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-2">
                        <label>Motivo / Observación</label>
                        <input type="text" id="ajuste-motivo" class="form-control" placeholder="Ej: Vencimiento, Daño, Corrección..." value="Ajuste manual">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Aplicar Ajuste',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#475569',
            preConfirm: () => {
                const cantidad = document.getElementById('ajuste-cantidad').value;
                if (cantidad <= 0) {
                    Swal.showValidationMessage('La cantidad debe ser mayor a 0');
                    return false;
                }

                const data = new FormData();
                data.append('idProducto', idProducto);
                data.append('cantidad', cantidad);
                data.append('tipo', document.getElementById('ajuste-tipo').value);
                data.append('motivo', document.getElementById('ajuste-motivo').value);

                return fetch('?controlador=inventario&accion=ajustarStock&dive=l6', {
                    method: 'POST',
                    body: data
                }).then(r => r.json());
            }
        }).then(res => {
            if (res.value && res.value.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: '¡Ajustado!',
                    text: res.value.msg,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else if (res.value) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: res.value.msg
                });
            }
        });
    }

    function verLotes(idProducto, nombreProducto) {
        Swal.fire({
            title: 'Desglose de Lotes',
            html: `
                <style>
                    .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                    .swal2-title { color: #f8fafc !important; font-size: 1.5rem;}
                    .tabla-lotes { width: 100%; color: #cbd5e1; text-align: left; margin-top: 15px; border-collapse: collapse; }
                    .tabla-lotes th { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; padding: 10px; border-bottom: 1px solid #334155; }
                    .tabla-lotes td { padding: 10px; border-bottom: 1px solid #334155; font-size: 0.9rem; }
                    .lote-primero { background: #10b98120; }
                </style>
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="background: #3b82f620; width: 70px; height: 70px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-layers" style="color:#3b82f6; font-size: 2rem;"></i>
                    </div>
                    <p style="color: #cbd5e1; margin-top: 10px; font-size: 1.1rem; font-weight: bold;">${nombreProducto}</p>
                </div>
                <div id="contenedor-tabla-lotes">
                    <p style="color: #94a3b8; text-align: center;">Cargando lotes...</p>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true
        });

        // Hacemos la petición AJAX
        fetch(`?controlador=inventario&accion=obtenerLotes&dive=l6&idProducto=${idProducto}`)
            .then(r => r.json())
            .then(lotes => {
                const contenedor = document.getElementById('contenedor-tabla-lotes');

                if (lotes.length === 0) {
                    contenedor.innerHTML = '<p style="color: #ef4444; text-align: center;">No hay lotes con stock disponible.</p>';
                    return;
                }

                let tablaHTML = `
                    <table class="tabla-lotes">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Vence</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                lotes.forEach((lote, index) => {
                    // El primer lote de la lista (por fecha ASC) es el próximo a vencerse
                    let claseFila = index === 0 ? 'lote-primero' : '';
                    let corona = index === 0 ? '<i class="bi bi-star-fill text-warning ms-1" title="Próximo a venderse (FIFO)"></i>' : '';

                    // Formatear la fecha
                    let fecha = new Date(lote.fecha_vencimiento);
                    let fechaStr = fecha.toLocaleDateString('es-CO', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    tablaHTML += `
                        <tr class="${claseFila}">
                            <td><b>${lote.codigo_lote}</b> ${corona}</td>
                            <td>${fechaStr}</td>
                            <td><span class="badge bg-success">${lote.cantidad_actual} uds</span></td>
                        </tr>
                    `;
                });

                tablaHTML += `</tbody></table>`;
                tablaHTML += `<p style="color: #64748b; font-size: 0.75rem; text-align: left; margin-top: 10px;"><i class="bi bi-info-circle"></i> El lote con la estrella es el próximo a salir en las ventas.</p>`;

                contenedor.innerHTML = tablaHTML;
            })
            .catch(err => {
                document.getElementById('contenedor-tabla-lotes').innerHTML = '<p style="color: #ef4444;">Error al cargar los lotes.</p>';
                console.error(err);
            });
    }
</script>