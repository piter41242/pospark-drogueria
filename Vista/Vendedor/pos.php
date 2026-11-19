<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>

<style>
    /* Estilos específicos para el POS */
    .pos-container { display: flex; gap: 20px; height: calc(100vh - 120px); }
    .pos-left { flex: 6; display: flex; flex-direction: column; }
    .pos-right { flex: 4; display: flex; flex-direction: column; }
    
    .buscador-pos { position: relative; margin-bottom: 15px; }
    .buscador-pos input { font-size: 1.2rem; padding: 15px 15px 15px 45px; border-radius: 12px; border: 2px solid #3b82f6; }
    .buscador-pos i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); font-size: 1.3rem; color: #3b82f6; }
    
    .lista-resultados { position: absolute; width: 100%; background: white; border-radius: 0 0 12px 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); z-index: 1000; max-height: 300px; overflow-y: auto; display: none; }
    .item-resultado { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .item-resultado:hover { background: #f8fafc; }
    
    .tabla-carrito { flex-grow: 1; overflow-y: auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .tabla-carrito table { width: 100%; }
    .tabla-carrito th { position: sticky; top: 0; background: #334155; color: white; padding: 15px; z-index: 10; }
    .tabla-carrito td { padding: 12px 15px; border-bottom: 1px solid #eee; }

    .totales-card { background: #1e293b; color: white; border-radius: 12px; padding: 20px; margin-top: 15px; }
    .total-row { display: flex; justify-content: space-between; font-size: 1.2rem; margin-bottom: 10px; }
    .total-final { font-size: 2rem; font-weight: 800; color: #10b981; border-top: 1px solid #334155; padding-top: 15px; margin-top: 15px; }
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
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="pos-tabla-body">
                    <?php foreach ($productos as $p): ?>
                        <tr id="fila-det-<?php echo $p['idDetalle']; ?>">
                            <td>
                                <b><?php echo htmlspecialchars($p['nombre']); ?></b><br>
                                <small class="text-muted">Lote: <?php echo $p['codigo_lote']; ?></small>
                            </td>
                            <td><?php echo $p['cantidad']; ?></td>
                            <td>$<?php echo number_format($p['valor'], 0); ?></td>
                            <td class="fw-bold">$<?php echo number_format($p['subtotal'], 0); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-danger" onclick="quitarProducto(<?php echo $p['idDetalle']; ?>)">
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
                <span id="txt-total">$<?php echo number_format($carrito['total'] ?? 0, 0); ?></span>
            </div>

            <div class="mt-auto pt-4">
                <button class="btn btn-success w-100 py-3 fw-bold fs-4" onclick="abrirModalCobro()" style="border-radius: 12px;">
                    <i class="bi bi-cash-coin me-2"></i> COBRAR
                </button>
            </div>
        </div>
    </div>
</div>

<script>
       const inputBuscador = document.getElementById('pos-buscador');
    const listaResultados = document.getElementById('pos-resultados');
    let productosFiltrados = []; // Guardamos los resultados aquí

    // Función principal de búsqueda
    inputBuscador.addEventListener('input', function() {
        const texto = this.value;
        if (texto.length < 2) {
            listaResultados.style.display = 'none';
            return;
        }

        fetch(`?controlador=inventario&accion=buscarProductosAjax&dive=l3&q=${encodeURIComponent(texto)}`)
            .then(r => r.json())
            .then(productos => {
                productosFiltrados = productos; // Guardamos para usarlos en el Enter
                listaResultados.innerHTML = '';
                
                if (productos.length > 0) {
                    listaResultados.style.display = 'block';
                    productos.forEach((prod, index) => {
                        const div = document.createElement('div');
                        div.className = 'item-resultado';
                        div.innerHTML = `
                            <div>
                                <b>${prod.nombre}</b><br>
                                <small class="text-muted">${prod.codigo_interno}</small>
                            </div>
                            <span class="badge bg-primary fs-6">$${new Intl.NumberFormat('es-CO').format(prod.precio)}</span>
                        `;
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

    // --- MAGIA: CAPTURAR LA TECLA ENTER ---
    inputBuscador.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Evita que el formulario se envíe
            
            if (productosFiltrados.length > 0) {
                // Si hay resultados en pantalla, agregamos el primero
                agregarAlCarrito(productosFiltrados[0].idProducto, 1);
            } else if (this.value.trim() !== '') {
                // Si el vendedor escaneó y no salió el menú (porque fue muy rápido), 
                // forzamos una búsqueda exacta
                fetch(`?controlador=inventario&accion=buscarProductosAjax&dive=l3&q=${encodeURIComponent(this.value.trim())}`)
                    .then(r => r.json())
                    .then(productos => {
                        if (productos.length > 0) {
                            agregarAlCarrito(productos[0].idProducto, 1);
                        } else {
                            Swal.fire({type: 'error', title: 'No encontrado', text: 'No se encontró ningún producto con ese código.', timer: 1500, showConfirmButton: false});
                        }
                    });
            }
        }
    });

    // --- AGREGAR PRODUCTO POR AJAX (Ahora acepta cantidad) ---
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
                // Limpiamos el buscador y recargamos la tabla de forma mágica
                inputBuscador.value = '';
                listaResultados.style.display = 'none';
                inputBuscador.focus();
                $("#idForm").load(location.href + " #idForm > *");
            } else {
                Swal.fire({type: 'error', title: 'Atención', text: res.msg, timer: 2000, showConfirmButton: false});
            }
        });
    }

    // --- ELIMINAR PRODUCTO DEL CARRITO ---
    function quitarProducto(idDetalle) {
        const formData = new FormData();
        formData.append('idDetalle', idDetalle);

        fetch('?controlador=carrito&accion=eliminarDelCarrito&dive=l3', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                $("#idForm").load(location.href + " #idForm > *");
            }
        });
    }

    // --- ELIMINAR PRODUCTO DEL CARRITO ---
    function quitarProducto(idDetalle) {
        const formData = new FormData();
        formData.append('idDetalle', idDetalle);

        fetch('?controlador=carrito&accion=eliminarDelCarrito&dive=l3', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                $("#idForm").load(location.href + " #idForm > *");
            }
        });
    }
</script>