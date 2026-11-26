<?php
if (!isset($_SESSION['usuario']['idUsuario'])) {
    header("Location:../../index.php");
    exit();
}
?>

<style>
    /* Estilos específicos para el POS */
    .pos-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 120px);
    }

    .pos-left {
        flex: 6;
        display: flex;
        flex-direction: column;
    }

    .pos-right {
        flex: 4;
        display: flex;
        flex-direction: column;
    }

    .buscador-pos {
        position: relative;
        margin-bottom: 15px;
    }

    .buscador-pos input {
        font-size: 1.2rem;
        padding: 15px 15px 15px 45px;
        border-radius: 12px;
        border: 2px solid #3b82f6;
    }

    .buscador-pos i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.3rem;
        color: #3b82f6;
    }

    .lista-resultados {
        position: absolute;
        width: 100%;
        background: white;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }

    .item-resultado {
        padding: 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .item-resultado:hover {
        background: #f8fafc;
    }

    .tabla-carrito {
        flex-grow: 1;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .tabla-carrito table {
        width: 100%;
    }

    .tabla-carrito th {
        position: sticky;
        top: 0;
        background: #334155;
        color: white;
        padding: 15px;
        z-index: 10;
        text-align: center;
    }

    .tabla-carrito th:nth-child(1) {
        text-align: left;
    }

    .tabla-carrito td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        text-align: center;
        vertical-align: middle;
    }

    .tabla-carrito td:nth-child(1) {
        text-align: left;
    }

    /* Estilos para la selección de fila */
    .fila-carrito {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .fila-carrito:hover {
        background-color: #f8fafc;
    }

    .fila-seleccionada {
        background-color: #e0f2fe !important;
        border-left: 4px solid #3b82f6;
    }

    .totales-card {
        background: #1e293b;
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-top: 15px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .total-final {
        font-size: 2rem;
        font-weight: 800;
        color: #10b981;
        border-top: 1px solid #334155;
        padding-top: 15px;
        margin-top: 15px;
    }
</style>

<div class="pos-container">
    <!-- LADO IZQUIERDO: BUSCADOR Y CARRITO -->
    <div class="pos-left">
        <div class="buscador-pos">
            <i class="bi bi-upc-scan"></i>
            <input type="text" id="pos-buscador" class="form-control" placeholder="Escanear código de barras o buscar por nombre..." autocomplete="off">
            <div id="pos-resultados" class="lista-resultados"></div>
        </div>

        <div class="tabla-carrito">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th width="150">Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                        <th width="60"></th>
                    </tr>
                </thead>
                <tbody id="pos-tabla-body">
                    <?php foreach ($productos as $p): ?>
                        <tr id="fila-det-<?php echo $p['idDetalle']; ?>" class="fila-carrito" onclick="seleccionarFila(this, <?php echo $p['idDetalle']; ?>)">
                            <td>
                                <b><?php echo htmlspecialchars($p['nombre']); ?></b><br>
                                <small class="text-muted">Lote: <?php echo $p['codigo_lote']; ?></small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); modificarCantidad(<?php echo $p['idDetalle']; ?>, -1)" title="Restar uno">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <span class="fw-bold fs-5" style="min-width: 30px;"><?php echo $p['cantidad']; ?></span>
                                    <button class="btn btn-sm btn-outline-success" onclick="event.stopPropagation(); modificarCantidad(<?php echo $p['idDetalle']; ?>, 1)" title="Sumar uno">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </td>
                            <td>$<?php echo number_format($p['valor'], 0); ?></td>
                            <td class="fw-bold">$<?php echo number_format($p['subtotal'], 0); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); quitarProducto(<?php echo $p['idDetalle']; ?>)" title="Eliminar del carrito">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($productos)): ?>
                <div class="text-center py-5 text-muted" id="carrito-vacio">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mt-2">El carrito está vacío. Escanea un producto para empezar.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LADO DERECHO: TOTALES Y COBRO -->
    <div class="pos-right">
        <div class="totales-card h-100 d-flex flex-column">
            <h4 class="mb-4">Resumen de Venta</h4>

            <div class="total-row">
                <span>Subtotal:</span>
                <span id="txt-subtotal">$<?php echo number_format($carrito['subtotal'] ?? 0, 0); ?></span>
            </div>
            <div class="total-row">
                <span>Descuento:</span>
                <span id="txt-descuento">$0</span>
            </div>
            <div class="total-row">
                <span>IVA:</span>
                <span id="txt-iva">$0</span>
            </div>

            <div class="total-final d-flex justify-content-between">
                <span>TOTAL:</span>
                <span id="txt-total" data-total="<?php echo $carrito['total'] ?? 0; ?>">$<?php echo number_format($carrito['total'] ?? 0, 0); ?></span>
            </div>

            <div class="mt-auto pt-4">
                <button class="btn btn-success w-100 py-3 fw-bold fs-4" onclick="abrirModalCobro()" style="border-radius: 12px;">
                    <i class="bi bi-cash-coin me-2"></i> COBRAR
                </button>
                <button class="btn btn-warning w-100 py-2 fw-bold fs-5" onclick="dejarEnEspera()" style="border-radius: 12px; color: #212529;">
                    <i class="bi bi-hourglass-split me-2"></i> DEJAR EN ESPERA
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modopagosOptions = `
        <?php foreach ($modopagos as $mp): ?>
            <option value="<?php echo $mp['idModopago']; ?>"><?php echo $mp['nombre']; ?></option>
        <?php endforeach; ?>
    `;
    const inputBuscador = document.getElementById('pos-buscador');
    const listaResultados = document.getElementById('pos-resultados');
    let productosFiltrados = [];
    let idDetalleSeleccionado = null;

    function seleccionarFila(elemento, idDetalle) {
        document.querySelectorAll('.fila-carrito').forEach(fila => fila.classList.remove('fila-seleccionada'));
        elemento.classList.add('fila-seleccionada');
        idDetalleSeleccionado = idDetalle;
    }

    inputBuscador.addEventListener('input', function() {
        const texto = this.value;
        if (texto.length < 2) {
            listaResultados.style.display = 'none';
            return;
        }

        fetch(`?controlador=inventario&accion=buscarProductosAjax&dive=l3&q=${encodeURIComponent(texto)}`)
            .then(r => r.json())
            .then(productos => {
                productosFiltrados = productos;
                listaResultados.innerHTML = '';
                if (productos.length > 0) {
                    listaResultados.style.display = 'block';
                    productos.forEach((prod) => {
                        const div = document.createElement('div');
                        div.className = 'item-resultado';
                        div.innerHTML = `<div><b>${prod.nombre}</b><br><small class="text-muted">${prod.codigo_interno}</small></div><span class="badge bg-primary fs-6">$${new Intl.NumberFormat('es-CO').format(prod.precio)}</span>`;
                        div.onclick = function() {
                            agregarAlCarrito(prod.idProducto, 1);
                        };
                        listaResultados.appendChild(div);
                    });
                } else {
                    listaResultados.style.display = 'none';
                }
            });
    });

    inputBuscador.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (productosFiltrados.length > 0) {
                agregarAlCarrito(productosFiltrados[0].idProducto, 1);
            } else if (this.value.trim() !== '') {
                fetch(`?controlador=inventario&accion=buscarProductosAjax&dive=l3&q=${encodeURIComponent(this.value.trim())}`)
                    .then(r => r.json())
                    .then(productos => {
                        if (productos.length > 0) {
                            agregarAlCarrito(productos[0].idProducto, 1);
                        } else {
                            Swal.fire({
                                type: 'error',
                                title: 'No encontrado',
                                text: 'No se encontró ningún producto.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
            }
        }
    });

    function agregarAlCarrito(idProducto, cantidad = 1) {
        const formData = new FormData();
        formData.append('idProducto', idProducto);
        formData.append('cantidad', cantidad);

        fetch('?controlador=carrito&accion=agregarAlCarrito&dive=l3', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    inputBuscador.value = '';
                    listaResultados.style.display = 'none';
                    inputBuscador.focus();
                    $("#idForm").load(location.href + " #idForm > *");
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Atención',
                        text: res.msg,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
    }

    function modificarCantidad(idDetalle, cambio) {
        const formData = new FormData();
        formData.append('idDetalle', idDetalle);
        formData.append('cambio', cambio);

        fetch('?controlador=carrito&accion=modificarCantidad&dive=l3', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(text => {
                try {
                    const res = JSON.parse(text);
                    if (res.status === 'success') {
                        $("#idForm").load(location.href + " #idForm > *", function() {
                            const fila = document.getElementById('fila-det-' + idDetalle);
                            if (fila) {
                                fila.classList.add('fila-seleccionada');
                                idDetalleSeleccionado = idDetalle;
                            }
                        });
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Atención',
                            text: res.msg,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                } catch (e) {
                    console.error("Error de PHP:", text);
                }
            });
    }

    function quitarProducto(idDetalle) {
        const formData = new FormData();
        formData.append('idDetalle', idDetalle);

        fetch('?controlador=carrito&accion=eliminarDelCarrito&dive=l3', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(text => {
                try {
                    const res = JSON.parse(text);
                    if (res.status === 'success') {
                        idDetalleSeleccionado = null;
                        $("#idForm").load(location.href + " #idForm > *");
                    }
                } catch (e) {
                    console.error("Error de PHP al eliminar:", text);
                }
            });
    }

    document.addEventListener('keydown', function(e) {
        if (document.activeElement === inputBuscador) return;
        if (e.key === '+' || e.key === 'Add') {
            e.preventDefault();
            if (idDetalleSeleccionado) {
                modificarCantidad(idDetalleSeleccionado, 1);
            }
        }
        if (e.key === '-' || e.key === 'Subtract') {
            e.preventDefault();
            if (idDetalleSeleccionado) {
                modificarCantidad(idDetalleSeleccionado, -1);
            }
        }
    });

    // --- COBRAR (VERSIÓN BLINDADA) ---
    function abrirModalCobro() {
        // Leemos el total directamente del DOM usando el atributo data-total que pusimos en el HTML
        const totalVenta = Math.round(parseFloat(document.getElementById('txt-total').getAttribute('data-total')) || 0);

        if (totalVenta <= 0) {
            Swal.fire({
                type: 'error',
                title: 'Carrito vacío',
                text: 'Agrega productos antes de cobrar.'
            });
            return;
        }

        Swal.fire({
            title: 'Cobrar Venta',
            html: `
                <style>
                    .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; }
                    .swal2-title { color: #f8fafc !important; }
                    .swal-premium-form { text-align: left; margin-top: 15px; }
                    .swal-premium-form label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
                    .swal-premium-form .form-control, .swal-premium-form .form-select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 10px 12px !important; font-size: 1.1rem; }
                    .cambio-texto { font-size: 2.5rem; font-weight: 800; color: #10b981; text-align: center; margin-top: 15px; }
                </style>
                <div style="text-align: center; margin-bottom: 15px;">
                    <p style="color: #94a3b8; font-size: 1rem; margin-bottom: 0;">Total a pagar:</p>
                    <h2 style="color: #f8fafc; font-size: 2.5rem; font-weight: 800; margin: 0;">$${new Intl.NumberFormat('es-CO').format(totalVenta)}</h2>
                </div>
                <form id="formCobro" class="swal-premium-form">
                    <div class="mb-3">
                        <label>Método de Pago *</label>
                                        <form id="formCobro" class="swal-premium-form">
                    <div class="mb-3">
                        <label>Método de Pago *</label>
                        <select id="cobro-modopago" class="form-select" onchange="cambiarMetodoPago(this.value, ${totalVenta})">
                            ${modopagosOptions}
                        </select>
                    </div>
                    
                    </div>
                    
                    <div id="seccion-efectivo">
                        <div class="mb-2">
                            <label>Paga Con *</label>
                            <input type="number" id="cobro-pagacon" class="form-control" value="${totalVenta}" min="${totalVenta}" oninput="calcularCambio(this.value, ${totalVenta})" onclick="this.select()">
                        </div>
                        <div class="cambio-texto" id="cobro-cambio">Cambio: $0</div>
                    </div>

                    <div id="seccion-credito" style="display: none;">
                        <div class="mb-2">
                            <label>Abono Inicial (Opcional)</label>
                            <input type="number" id="cobro-abono" class="form-control" value="0" min="0" max="${totalVenta}" oninput="validarAbono(this.value)">
                        </div>
                        <div class="mb-2" id="div-medio-abono" style="display: none;">
                            <label>Medio de Pago del Abono</label>
                            <select id="cobro-medio-abono" class="form-select">
                                <option value="1">Efectivo</option>
                                <option value="3">Nequi</option>
                                <option value="4">Daviplata</option>
                                <option value="8">Tarjeta</option>
                            </select>
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirmar Pago',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#475569',
            width: '450px',
            preConfirm: () => {
                const modopago = document.getElementById('cobro-modopago').value;
                const pagacon = parseFloat(document.getElementById('cobro-pagacon').value) || 0;
                const abono = parseFloat(document.getElementById('cobro-abono').value) || 0;
                const medioAbono = document.getElementById('cobro-medio-abono').value;

                if (modopago == '1' && pagacon < totalVenta) {
                    Swal.showValidationMessage('El monto pagado es menor al total de la venta.');
                    return false;
                }

                const data = new FormData();
                data.append('modopago', modopago);
                if (modopago == '1') {
                    data.append('pagacon', pagacon);
                } else if (modopago == '2') {
                    data.append('pagacon', abono);
                    data.append('medio_abono', medioAbono);
                } else {
                    data.append('pagacon', totalVenta);
                }

                return fetch('?controlador=carrito&accion=cobrarVenta&dive=l3', {
                    method: 'POST',
                    body: data
                }).then(r => r.json());
            }
        }).then(res => {
            if (res.value && res.value.status === 'success') {
                Swal.fire({
                        type: 'success',
                        title: '¡Venta Exitosa!',
                        text: `Venta #${res.value.idVenta} registrada.`,
                        timer: 2000,
                        showConfirmButton: false
                    })
                    .then(() => {
                        $("#idForm").load(location.href + " #idForm > *");
                    });
            } else if (res.value) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: res.value.msg
                });
            }
        });
    }

    function cambiarMetodoPago(metodo, total) {
        const seccionEfectivo = document.getElementById('seccion-efectivo');
        const seccionCredito = document.getElementById('seccion-credito');

        // Los IDs según tu base de datos: 1=Efectivo, 2=Crédito. 
        // Los demás (3 al 8) son transferencias/tarjetas.

        if (metodo == '1') {
            seccionEfectivo.style.display = 'block';
            seccionCredito.style.display = 'none';
            setTimeout(() => {
                document.getElementById('cobro-pagacon').select();
            }, 100);
        } else if (metodo == '2') {
            seccionEfectivo.style.display = 'none';
            seccionCredito.style.display = 'block';
        } else {
            // Para cualquier otro método (Nequi, Daviplata, Tarjeta, etc.)
            seccionEfectivo.style.display = 'none';
            seccionCredito.style.display = 'none';
        }
    }

    function calcularCambio(pagacon, total) {
        const cambio = parseFloat(pagacon) - total;
        const txtCambio = document.getElementById('cobro-cambio');
        if (cambio >= 0) {
            txtCambio.innerText = `Cambio: $${new Intl.NumberFormat('es-CO').format(cambio)}`;
            txtCambio.style.color = '#10b981';
        } else {
            txtCambio.innerText = `Faltan: $${new Intl.NumberFormat('es-CO').format(Math.abs(cambio))}`;
            txtCambio.style.color = '#ef4444';
        }
    }

    function validarAbono(abono) {
        const divMedioAbono = document.getElementById('div-medio-abono');
        if (parseFloat(abono) > 0) {
            divMedioAbono.style.display = 'block';
        } else {
            divMedioAbono.style.display = 'none';
        }
    }
    // --- DEJAR CARRITO EN ESPERA ---
    function dejarEnEspera() {
        Swal.fire({
            title: '¿Dejar en espera?',
            text: 'El carrito se guardará en "Carritos Abiertos" y se iniciará uno nuevo para el siguiente cliente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                // No necesitamos mandar el ID, el controlador lo busca por sesión

                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('?controlador=carrito&accion=dejarEnEspera&dive=l3', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                    type: 'success',
                                    title: '¡Guardado!',
                                    text: res.msg,
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(() => {
                                    // Recargamos el POS. Como el carrito quedó en NULL, el controlador creará uno nuevo vacío automáticamente.
                                    $("#idForm").load(location.href + " #idForm > *");
                                });
                        } else {
                            Swal.fire({
                                type: 'error',
                                title: 'Error',
                                text: res.msg
                            });
                        }
                    });
            }
        });
    }
</script>