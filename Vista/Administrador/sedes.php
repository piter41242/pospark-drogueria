<?php
$rol_actual = (int)$_SESSION['usuario']['permiso'];
$puede_editar = in_array($rol_actual, [1, 5]);

if (!isset($_SESSION['usuario']['idUsuario'])) {
    header("Location:../../index.php");
    exit();
}

$busqueda = $_GET['buscar'] ?? '';
$modeloNegocio = new Negocio();
$negocios = ($_SESSION['usuario']['permiso'] == 1) ? $modeloNegocio->consultarTodos() : [$modeloNegocio->buscarId($sedeActual['idNegocio'] ?? 1)];
?>

<style>
    /* 1. ESTILOS DE LA TABLA */
    .card-custom {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .tabla-custom thead th {
        background: #1e293b;
        /* Cabecera oscura para contraste */
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

    /* 2. BOTONES DE ACCIÓN (Alto contraste) */
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

    .btn-edit {
        background: #3b82f6;
    }

    /* Azul sólido */
    .btn-edit:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-delete {
        background: #ef4444;
    }

    /* Rojo sólido */
    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(239, 68, 68, 0.3);
    }

    /* Responsive */
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

    /* 3. ESTILOS PARA SWEETALERT (Fondo Oscuro Premium) */
    .swal-premium-popup {
        background: #1e293b !important;
        /* Fondo oscuro elegante */
        border-radius: 20px !important;
        border: 1px solid #334155;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }

    .swal-premium-title {
        color: #f8fafc !important;
        font-weight: 700 !important;
        font-size: 1.5rem !important;
    }

    .swal-premium-form label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }

    .swal-premium-form .form-control,
    .swal-premium-form .form-select {
        background-color: #0f172a;
        /* Inputs oscuros */
        border: 1px solid #334155;
        color: #f8fafc;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .swal-premium-form .form-control:focus,
    .swal-premium-form .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        outline: none;
    }

    .swal-premium-form input::placeholder {
        color: #64748b;
    }

    /* Botones de SweetAlert */
    .swal2-confirm,
    .swal2-cancel {
        border-radius: 10px !important;
        font-weight: 600 !important;
        padding: 10px 24px !important;
    }
</style>

<!-- Encabezado -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8 col-6">
        <h3 class="fw-bold mb-0" style="color:#0f172a;"><i class="bi bi-shop me-2" style="color:#10b981;"></i> Gestión de Sedes</h3>
        <h6 class="text-muted mb-0 d-none d-md-block" style="font-size: 1rem; color: #0e264b;">Administra las sedes de tu negocio.</h6>
    </div>
    <div class="col-md-4 col-6 text-end">
        <?php if ($puede_editar): ?>
            <button onclick="formularioSede()" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm" style="background: #10b981; border:none;">
                <i class="bi bi-plus-circle me-1"></i> Nueva Sede
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador Estilizado -->
<div class="card card-custom mb-4" style="background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);">
    <div class="card-body p-2">
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="controlador" value="sede">
            <input type="hidden" name="accion" value="inicio">
            <input type="hidden" name="dive" value="l4">

            <!-- Input con ícono integrado -->
            <div class="position-relative flex-grow-1">
                <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem;"></i>
                <input type="text" name="buscar" class="form-control border-0 shadow-none ps-5 py-2"
                    placeholder="Buscar por nombre, ciudad o dirección..."
                    value="<?php echo htmlspecialchars($busqueda); ?>"
                    style="background: #f1f5f9; border-radius: 12px; font-size: 0.95rem; color: #1e293b;">
            </div>

            <!-- Botón Buscar moderno -->
            <button class="btn btn-primary px-4 py-2 fw-bold" style="background: #3b82f6; border:none; border-radius: 12px; transition: all 0.3s;">
                Buscar
            </button>

            <?php if (!empty($busqueda)): ?>
                <!-- Botón para limpiar búsqueda si hay un filtro activo -->
                <a href="?controlador=sede&accion=inicio&dive=l4" class="btn btn-light px-3 py-2 fw-bold text-muted" style="border-radius: 12px; border: 1px solid #e2e8f0;" title="Limpiar búsqueda">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card card-custom" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="table-responsive">
        <table class="table tabla-custom mb-0" id="tablaSedes" style="border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="background: #334155; color: #f8fafc; border-radius: 16px 0 0 0;">#</th>
                    <th style="background: #334155; color: #f8fafc;">Nombre</th>
                    <th style="background: #334155; color: #f8fafc;">Dirección</th>
                    <th style="background: #334155; color: #f8fafc;">Ciudad</th>
                    <th style="background: #334155; color: #f8fafc;">Teléfono</th>
                    <th style="background: #334155; color: #f8fafc;">Cód. DANE</th>
                    <th style="background: #334155; color: #f8fafc;">Estado</th>
                    <th class="text-center" style="background: #334155; color: #f8fafc; border-radius: 0 16px 0 0;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sedes)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay sedes registradas.</td>
                    </tr>
                    <?php else: foreach ($sedes as $s): ?>
                        <tr id="fila-sede-<?php echo $s['idSede']; ?>">
                            <td data-label="#" class="fw-bold" style="color: #94a3b8; border-bottom: 1px solid #e2e8f0;"><?php echo $s['idSede']; ?></td>
                            <td data-label="Nombre" class="fw-bold" style="color: #0f172a; border-bottom: 1px solid #e2e8f0;"><?php echo htmlspecialchars($s['nombre']); ?></td>
                            <td data-label="Dirección" style="color: #475569; border-bottom: 1px solid #e2e8f0;"><?php echo htmlspecialchars($s['direccion']); ?></td>
                            <td data-label="Ciudad" style="color: #475569; border-bottom: 1px solid #e2e8f0;"><?php echo htmlspecialchars($s['ciudad']); ?></td>
                            <td data-label="Teléfono" style="color: #475569; border-bottom: 1px solid #e2e8f0;"><?php echo htmlspecialchars($s['telefono']); ?></td>
                            <td data-label="DANE" style="color: #475569; border-bottom: 1px solid #e2e8f0;"><?php echo htmlspecialchars($s['codigo_dane'] ?? '—'); ?></td>
                            <td data-label="Estado" style="border-bottom: 1px solid #e2e8f0;">
                                <?php if ($s['estado'] == 1): ?>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #047857; padding: 6px 12px; font-weight: 600;">Activa</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background: #f1f5f9; color: #64748b; padding: 6px 12px; font-weight: 600;">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-md-end" style="border-bottom: 1px solid #e2e8f0;">
                                <?php if ($puede_editar): ?>
                                    <!-- Si es Superadmin (5) o Desarrollador (1), ve los botones -->
                                    <button class="btn btn-action btn-edit me-1" title="Editar"
                                        onclick="formularioSede(
                                        <?php echo $s['idSede']; ?>, 
                                        '<?php echo htmlspecialchars($s['nombre'], ENT_QUOTES); ?>', 
                                        '<?php echo htmlspecialchars($s['direccion'], ENT_QUOTES); ?>', 
                                        '<?php echo htmlspecialchars($s['ciudad'], ENT_QUOTES); ?>', 
                                        '<?php echo htmlspecialchars($s['telefono'], ENT_QUOTES); ?>', 
                                        '<?php echo htmlspecialchars($s['codigo_dane'] ?? '', ENT_QUOTES); ?>', 
                                        <?php echo $s['estado']; ?>
                                        )">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-action btn-delete" title="Eliminar"
                                        onclick="eliminarSede(<?php echo $s['idSede']; ?>, '<?php echo htmlspecialchars($s['nombre'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- Si es Admin de Sede (2), ve un mensaje de solo lectura -->
                                    <span class="badge bg-light text-secondary" style="font-size: 0.75rem;">
                                        <i class="bi bi-eye-fill me-1"></i>Solo lectura
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Paginador Estilizado -->
<?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div class="card card-custom mt-3 border-0" style="background: #f8fafc;">
        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

            <!-- Texto de información -->
            <div class="text-muted" style="font-size: 0.9rem; font-weight: 500;">
                Mostrando <span class="fw-bold" style="color: #0f172a;"><?php echo $inicio + 1; ?></span>
                a <span class="fw-bold" style="color: #0f172a;"><?php echo min($inicio + $porPagina, $totalRegistros); ?></span>
                de <span class="fw-bold" style="color: #0f172a;"><?php echo $totalRegistros; ?></span> sedes
            </div>

            <!-- Botones del paginador -->
            <nav>
                <ul class="pagination pagination-sm mb-0" style="gap: 5px;">
                    <!-- Anterior -->
                    <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center"
                            href="?controlador=sede&accion=inicio&dive=l4&pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($busqueda); ?>"
                            style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.2s;">
                            <i class="bi bi-chevron-left" style="font-size: 0.9rem;"></i>
                        </a>
                    </li>

                    <!-- Números -->
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item">
                            <a class="page-link border-0 d-flex align-items-center justify-content-center <?php echo ($pagina == $i) ? 'fw-bold text-white' : ''; ?>"
                                href="?controlador=sede&accion=inicio&dive=l4&pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>"
                                style="<?php echo ($pagina == $i) ? 'background: #3b82f6; box-shadow: 0 4px 10px rgba(59,130,246,0.3);' : 'color: #475569; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'; ?> 
                                  width: 36px; height: 36px; border-radius: 10px; transition: all 0.2s;"
                                onmouseover="this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.transform='translateY(0)'">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Siguiente -->
                    <li class="page-item <?php echo ($pagina >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center"
                            href="?controlador=sede&accion=inicio&dive=l4&pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($busqueda); ?>"
                            style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.2s;">
                            <i class="bi bi-chevron-right" style="font-size: 0.9rem;"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>

<script>
    const negociosOptions = `
    <?php foreach ($negocios as $n): ?>
        <option value="<?php echo $n['idNegocio']; ?>"><?php echo htmlspecialchars($n['razon_social'], ENT_QUOTES); ?></option>
    <?php endforeach; ?>
`;

    function formularioSede(id = null, nombre = '', direccion = '', ciudad = '', telefono = '', dane = '', estado = 1) {
        const isEdit = id !== null;
        const titulo = isEdit ? 'Editar Sede' : 'Nueva Sede';
        const btnText = isEdit ? 'Actualizar' : 'Guardar';
        const colorBtn = isEdit ? '#f59e0b' : '#10b981';
        const icono = isEdit ? 'bi-pencil-square' : 'bi-shop';

        Swal.fire({
            title: titulo,
            html: `
            <style>
                .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; border: 1px solid #334155 !important; }
                .swal2-title { color: #f8fafc !important; font-weight: 700 !important; }
                .swal-premium-form { text-align: left; margin-top: 15px; }
                .swal-premium-form label { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; display: block; }
                .swal-premium-form .form-control, .swal-premium-form .form-select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 10px 14px !important; width: 100%; box-sizing: border-box; }
                .swal-premium-form .form-control:focus, .swal-premium-form .form-select:focus { border-color: ${colorBtn} !important; box-shadow: 0 0 0 4px ${colorBtn}33 !important; outline: none; }
                .swal-premium-form input::placeholder { color: #64748b !important; }
                .swal2-validation-message { background: #ef4444 !important; color: white !important; }
            </style>

            <div style="text-align: center; margin-bottom: 20px;">
                <div style="background: ${colorBtn}20; width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi ${icono}" style="color:${colorBtn}; font-size: 2.5rem;"></i>
                </div>
            </div>

            <form id="formSede" class="swal-premium-form">
                <input type="hidden" id="swal-idSede" value="${id || ''}">
                
                <div class="mb-3">
                    <label>Negocio *</label>
                    <select id="swal-idNegocio" class="form-select">${negociosOptions}</select>
                </div>
                <div class="mb-3">
                    <label>Nombre de la sede *</label>
                    <input type="text" id="swal-nombre" class="form-control" value="${nombre}" placeholder="Ej: Sede Norte" required>
                </div>
                <div class="mb-3">
                    <label>Dirección *</label>
                    <input type="text" id="swal-direccion" class="form-control" value="${direccion}" placeholder="Ej: Calle 1 # 1-1" required>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label>Ciudad</label>
                        <input type="text" id="swal-ciudad" class="form-control" value="${ciudad}" placeholder="Ej: Bogotá">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Teléfono</label>
                        <input type="text" id="swal-telefono" class="form-control" value="${telefono}" placeholder="Ej: 3000000000">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label>Cód. DANE</label>
                        <input type="text" id="swal-codigo_dane" class="form-control" value="${dane}" placeholder="Opcional">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Estado *</label>
                        <select id="swal-estado" class="form-select">
                            <option value="1" ${estado == 1 ? 'selected' : ''}>Activa</option>
                            <option value="2" ${estado == 2 ? 'selected' : ''}>Inactiva</option>
                        </select>
                    </div>
                </div>
            </form>
        `,
            showCancelButton: true,
            confirmButtonText: btnText,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: colorBtn,
            cancelButtonColor: '#475569',
            width: '600px',
            focusConfirm: false,
            preConfirm: () => {
                const nom = document.getElementById('swal-nombre').value;
                const dir = document.getElementById('swal-direccion').value;

                if (!nom || !dir) {
                    Swal.showValidationMessage(`El nombre y la dirección son obligatorios`);
                    return false;
                }

                return {
                    idSede: document.getElementById('swal-idSede').value,
                    idNegocio: document.getElementById('swal-idNegocio').value,
                    nombre: nom,
                    direccion: dir,
                    ciudad: document.getElementById('swal-ciudad').value,
                    telefono: document.getElementById('swal-telefono').value,
                    codigo_dane: document.getElementById('swal-codigo_dane').value,
                    estado: document.getElementById('swal-estado').value,
                    isEdit: isEdit
                };
            }
        }).then((result) => {
            if (!result.value) return; // Si cancela, no hace nada

            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                }
            });

            const data = result.value;
            const formData = new FormData();
            formData.append('idSede', data.idSede);
            formData.append('idNegocio', data.idNegocio);
            formData.append('nombre', data.nombre);
            formData.append('direccion', data.direccion);
            formData.append('ciudad', data.ciudad);
            formData.append('telefono', data.telefono);
            formData.append('codigo_dane', data.codigo_dane);
            formData.append('estado', data.estado);

            const accion = data.isEdit ? 'editar' : 'crear';

            fetch(`?controlador=sede&accion=${accion}&dive=l4`, {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error("Error interno del servidor.");
                    }
                })
                .then(res => {
                    if (res.status === 'error') {
                        // Alerta de Error (Sin customClass)
                        Swal.fire({
                            type: 'error',
                            title: 'Error',
                            text: res.msg,
                            confirmButtonColor: '#ef4444'
                        });
                    } else {
                        // Alerta de Éxito (Sin customClass)
                        Swal.fire({
                            type: 'success',
                            title: '¡Éxito!',
                            text: res.msg,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // RECARGA MÁGICA DE LA TABLA SIN REFRESCAR LA PÁGINA
                            if (typeof jQuery !== 'undefined') {
                                $("#idForm").load(location.href + " #idForm > *");
                            } else {
                                window.location.reload();
                            }
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        type: 'error',
                        title: 'Error inesperado',
                        text: error.message,
                        confirmButtonColor: '#ef4444'
                    });
                });
        });
    }

    function eliminarSede(id, nombre) {
        Swal.fire({
            title: '¿Eliminar sede?',
            html: `
            <style>
                .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; border: 1px solid #334155 !important; }
                .swal2-title { color: #f8fafc !important; font-weight: 700 !important; }
            </style>
            <div style="text-align:center;">
                <div style="background: #ef444420; width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444; font-size: 3rem;"></i>
                </div>
                <p style="color: #cbd5e1; font-size: 1.05rem; margin-bottom: 5px;">
                    Estás a punto de eliminar la sede <br><b style="color: #f8fafc;">${nombre}</b>.
                </p>
                <p style="color: #94a3b8; font-size: 0.85rem;">Esta acción no se puede deshacer.</p>
            </div>
        `,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: 'Eliminando...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('id', id);

                fetch('?controlador=sede&accion=eliminar&dive=l4', {
                        method: 'POST',
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error("Error interno del servidor.");
                        }
                    })
                    .then(res => {
                        if (res.status === 'error') {
                            Swal.fire({
                                type: 'error',
                                title: 'No se puede eliminar',
                                text: res.msg,
                                confirmButtonColor: '#ef4444'
                            });
                        } else {
                            Swal.fire({
                                type: 'success',
                                title: '¡Eliminada!',
                                text: res.msg,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // RECARGA MÁGICA DE LA TABLA SIN REFRESCAR LA PÁGINA
                                if (typeof jQuery !== 'undefined') {
                                    $("#idForm").load(location.href + " #idForm > *");
                                } else {
                                    const fila = document.getElementById('fila-sede-' + id);
                                    if (fila) {
                                        fila.style.transition = 'all 0.4s ease';
                                        fila.style.opacity = '0';
                                        setTimeout(() => fila.remove(), 400);
                                    }
                                }
                            });
                        }
                    });
            }
        });
    }
</script>