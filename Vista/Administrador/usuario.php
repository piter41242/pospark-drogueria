<?php
if (!isset($_SESSION['usuario']['idUsuario'])) {
    header("Location:../../index.php");
    exit();
}
$busqueda = $_GET['buscar'] ?? '';
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

    .btn-edit {
        background: #3b82f6;
    }

    .btn-edit:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #ef4444;
    }

    .btn-delete:hover {
        background: #dc2626;
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

<!-- Encabezado -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8 col-6">
        <h3 class="fw-bold mb-0" style="color:#0f172a;"><i class="bi bi-people-fill me-2" style="color:#10b981;"></i> Gestión de Usuarios</h3>
        <p class="text-muted mb-0 d-none d-md-block" style="font-size: 0.95rem;">Administra el personal de tu sede.</p>
    </div>
    <div class="col-md-4 col-6 text-end">
        <button onclick="formularioUsuario()" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm" style="background: #10b981; border:none;">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </button>
    </div>
</div>

<!-- Buscador -->
<div class="card card-custom mb-4" style="background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);">
    <div class="card-body p-2">
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="controlador" value="usuario">
            <input type="hidden" name="accion" value="usuario">
            <input type="hidden" name="dive" value="l7">
            <div class="position-relative flex-grow-1">
                <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); font-size: 1.1rem;"></i>
                <input type="text" name="buscar" class="form-control border-0 shadow-none ps-5 py-2" placeholder="Buscar por nombre o usuario..." value="<?php echo htmlspecialchars($busqueda); ?>" style="background: #f1f5f9; border-radius: 12px; font-size: 0.95rem; color: #1e293b;">
            </div>
            <button class="btn btn-primary px-4 py-2 fw-bold" style="background: #3b82f6; border:none; border-radius: 12px;">Buscar</button>
            <?php if (!empty($busqueda)): ?>
                <a href="?controlador=usuario&accion=usuario&dive=l7" class="btn btn-light px-3 py-2 fw-bold text-muted" style="border-radius: 12px; border: 1px solid #e2e8f0;" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card card-custom" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="table-responsive">
        <table class="table tabla-custom mb-0" id="tablaUsuarios" style="border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border-radius: 16px 0 0 0;">#</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th class="text-center" style="border-radius: 0 16px 0 0;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay usuarios registrados.</td>
                    </tr>
                    <?php else: foreach ($usuarios as $u): ?>
                        <tr id="fila-user-<?php echo $u['idUsuario']; ?>">
                            <td data-label="#" class="fw-bold" style="color: #94a3b8;"><?php echo $u['idUsuario']; ?></td>
                            <td data-label="Nombre" class="fw-bold" style="color: #0f172a;"><?php echo htmlspecialchars($u['nombre']); ?></td>
                            <td data-label="Usuario" style="color: #475569;"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($u['usuario']); ?></td>
                            <td data-label="Rol"><span class="badge" style="background: #e0e7ff; color: #4338ca;"><?php echo htmlspecialchars($u['nombrePermiso']); ?></span></td>
                            <td data-label="Contacto" style="color: #475569;">
                                <div style="font-size:0.85rem;"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($u['telefono']); ?></div>
                                <div style="font-size:0.85rem;"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($u['email'] ?? 'N/A'); ?></div>
                            </td>
                            <td data-label="Estado">
                                <?php if ($u['estado'] == 1): ?>
                                    <span class="badge rounded-pill" style="background: #d1fae5; color: #047857; padding: 6px 12px;">Activo</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background: #f1f5f9; color: #64748b; padding: 6px 12px;">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-md-end">
                                <a class="btn btn-action btn-edit me-1" title="Editar"
                                    data-id="<?php echo $u['idUsuario']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($u['nombre'] ?? '', ENT_QUOTES); ?>"
                                    data-usuario="<?php echo htmlspecialchars($u['usuario'] ?? '', ENT_QUOTES); ?>"
                                    data-telefono="<?php echo htmlspecialchars($u['telefono'] ?? '', ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($u['email'] ?? '', ENT_QUOTES); ?>"
                                    data-permiso="<?php echo $u['permiso'] ?? 3; ?>"
                                    data-estado="<?php echo $u['estado'] ?? 1; ?>"
                                    data-sede="<?php echo $u['idSede']; ?>"
                                    onclick="abrirEditarUsuario(this)"
                                    style="cursor: pointer;">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-action btn-delete" title="Eliminar"
                                    onclick="eliminarUsuario(<?php echo $u['idUsuario']; ?>, '<?php echo htmlspecialchars($u['nombre'], ENT_QUOTES); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
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
            <div class="text-muted" style="font-size: 0.9rem; font-weight: 500;">
                Mostrando <span class="fw-bold" style="color: #0f172a;"><?php echo $inicio + 1; ?></span>
                a <span class="fw-bold" style="color: #0f172a;"><?php echo min($inicio + $porPagina, $totalRegistros); ?></span>
                de <span class="fw-bold" style="color: #0f172a;"><?php echo $totalRegistros; ?></span> usuarios
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" style="gap: 5px;">
                    <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=usuario&accion=usuario&dive=l7&pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($busqueda); ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item">
                            <a class="page-link border-0 d-flex align-items-center justify-content-center <?php echo ($pagina == $i) ? 'fw-bold text-white' : ''; ?>" href="?controlador=usuario&accion=usuario&dive=l7&pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>" style="<?php echo ($pagina == $i) ? 'background: #3b82f6; box-shadow: 0 4px 10px rgba(59,130,246,0.3);' : 'color: #475569; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'; ?> width: 36px; height: 36px; border-radius: 10px;"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($pagina >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 d-flex align-items-center justify-content-center" href="?controlador=usuario&accion=usuario&dive=l7&pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($busqueda); ?>" style="color: #475569; background: #fff; width: 36px; height: 36px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>

<script>
    // 1. Pasamos las variables de PHP a JS
    const rolActual = <?php echo $rol_actual; ?>;
    const sedesDisponibles = <?php echo json_encode($sedesDisponibles ?? []); ?>;

    // 2. Generamos las opciones del Select de sedes
    let sedesOptions = '';
    sedesDisponibles.forEach(sede => {
        sedesOptions += `<option value="${sede.idSede}">${sede.nombre}</option>`;
    });

    function abrirEditarUsuario(elemento) {
        const id = elemento.getAttribute('data-id');
        const nombre = elemento.getAttribute('data-nombre') || '';
        const usuario = elemento.getAttribute('data-usuario') || '';
        const telefono = elemento.getAttribute('data-telefono') || '';
        const email = elemento.getAttribute('data-email') || '';
        const permiso = elemento.getAttribute('data-permiso');
        const estado = elemento.getAttribute('data-estado');
        const idSede = elemento.getAttribute('data-sede'); // Leemos la sede actual

        formularioUsuario(id, nombre, usuario, telefono, email, permiso, estado, idSede);
    }

    function formularioUsuario(id = null, nombre = '', usuario = '', telefono = '', email = '', permiso = 3, estado = 1, idSedeActual = null) {
        const isEdit = id !== null;
        permiso = parseInt(permiso);
        estado = parseInt(estado);

        const titulo = isEdit ? 'Editar Usuario' : 'Nuevo Usuario';
        const btnText = isEdit ? 'Actualizar' : 'Guardar';
        const colorBtn = isEdit ? '#f59e0b' : '#10b981';
        const icono = isEdit ? 'bi-person-gear' : 'bi-person-plus-fill';
        const labelClave = isEdit ? 'Contraseña (Dejar en blanco para no cambiar)' : 'Contraseña *';

        // Si es Superadmin (rol 2), mostramos el selector de sedes
        let htmlSede = '';
        if (rolActual === 5) { // <-- Cambiado a 5
            htmlSede = `
        <div class="mb-2">
            <label>Sede Asignada *</label>
            <select id="swal-idSede" class="form-select">${sedesOptions}</select>
        </div>
    `;
        }

        Swal.fire({
            title: titulo,
            html: `
            <style>
                .swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; border: 1px solid #334155 !important; }
                .swal2-title { color: #f8fafc !important; font-weight: 700 !important; }
                .swal-premium-form { text-align: left; margin-top: 15px; }
                .swal-premium-form label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
                .swal-premium-form .form-control, .swal-premium-form .form-select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: #f8fafc !important; border-radius: 10px !important; padding: 8px 12px !important; width: 100%; box-sizing: border-box; font-size: 0.9rem; }
                .swal-premium-form .form-control:focus, .swal-premium-form .form-select:focus { border-color: ${colorBtn} !important; box-shadow: 0 0 0 4px ${colorBtn}33 !important; outline: none; }
                .swal-premium-form input::placeholder { color: #64748b !important; }
            </style>

            <div style="text-align: center; margin-bottom: 15px;">
                <div style="background: ${colorBtn}20; width: 70px; height: 70px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi ${icono}" style="color:${colorBtn}; font-size: 2.2rem;"></i>
                </div>
            </div>

            <form id="formUsuario" class="swal-premium-form">
                <input type="hidden" id="swal-idUsuario" value="${id || ''}">
                
                <div class="mb-2">
                    <label>Nombre Completo *</label>
                    <input type="text" id="swal-nombre" class="form-control" value="${nombre}" placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label>Usuario (Login) *</label>
                        <input type="text" id="swal-usuario" class="form-control" value="${usuario}" placeholder="Ej: juanp" required>
                    </div>
                    <div class="col-6">
                        <label>${labelClave}</label>
                        <input type="password" id="swal-clave" class="form-control" placeholder="••••••••" ${isEdit ? '' : 'required'}>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label>Teléfono</label>
                        <input type="text" id="swal-telefono" class="form-control" value="${telefono}" placeholder="Ej: 300...">
                    </div>
                    <div class="col-6">
                        <label>Email</label>
                        <input type="email" id="swal-email" class="form-control" value="${email}" placeholder="correo@ejemplo.com">
                    </div>
                </div>
                
                ${htmlSede}  <!-- AQUÍ INYECTAMOS EL SELECT DE SEDES SI ES SUPERADMIN -->

                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label>Rol *</label>
                        <select id="swal-permiso" class="form-select">
                            <option value="2" ${permiso == 2 ? 'selected' : ''}>Administrador (Sede)</option>
                            <option value="3" ${permiso == 3 ? 'selected' : ''}>Vendedor</option>
                            <option value="4" ${permiso == 4 ? 'selected' : ''}>Regente</option>
                            <!-- Si quieres que un Superadmin pueda crear otro Superadmin, agrega esta línea: -->
                            <!-- <option value="5" ${permiso == 5 ? 'selected' : ''}>Superadmin</option> -->
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Estado *</label>
                        <select id="swal-estado" class="form-select">
                            <option value="1" ${estado == 1 ? 'selected' : ''}>Activo</option>
                            <option value="2" ${estado == 2 ? 'selected' : ''}>Bloqueado</option>
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
            width: '650px',
            focusConfirm: false,
            didOpen: () => {
                // Seleccionar la sede del usuario si estamos editando (solo para Superadmin)
                if (isEdit && rolActual === 2 && idSedeActual) {
                    const selectSede = document.getElementById('swal-idSede');
                    if (selectSede) selectSede.value = idSedeActual;
                }
            },
            preConfirm: () => {
                const nom = document.getElementById('swal-nombre').value;
                const usr = document.getElementById('swal-usuario').value;
                const clv = document.getElementById('swal-clave').value;

                if (!nom || !usr || (!isEdit && !clv)) {
                    Swal.showValidationMessage(`Nombre, usuario y contraseña (al crear) son obligatorios`);
                    return false;
                }

                let sedeValue = null;
                if (rolActual === 2) {
                    sedeValue = document.getElementById('swal-idSede').value;
                }

                return {
                    idUsuario: document.getElementById('swal-idUsuario').value,
                    nombre: nom,
                    usuario: usr,
                    clave: clv,
                    telefono: document.getElementById('swal-telefono').value,
                    email: document.getElementById('swal-email').value,
                    permiso: document.getElementById('swal-permiso').value,
                    estado: document.getElementById('swal-estado').value,
                    idSede: sedeValue, // Enviamos la sede
                    isEdit: isEdit
                };
            }
        }).then((result) => {
            if (!result.value) return;
            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                }
            });

            const data = result.value;
            const formData = new FormData();
            for (const key in data) {
                if (key !== 'isEdit' && data[key] !== null) formData.append(key, data[key]);
            }

            const accion = data.isEdit ? 'editar' : 'crear';

            fetch(`?controlador=usuario&accion=${accion}&dive=l7`, {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("Respuesta del servidor:", text);
                        throw new Error("Error interno del servidor.");
                    }
                })
                .then(res => {
                    if (res.status === 'error') {
                        Swal.fire({
                            type: 'error',
                            title: 'Error',
                            text: res.msg,
                            confirmButtonColor: '#ef4444'
                        });
                    } else {
                        Swal.fire({
                                type: 'success',
                                title: '¡Éxito!',
                                text: res.msg,
                                timer: 1500,
                                showConfirmButton: false
                            })
                            .then(() => {
                                if (typeof jQuery !== 'undefined') $("#idForm").load(location.href + " #idForm > *");
                                else window.location.reload();
                            });
                    }
                })
                .catch(error => Swal.fire({
                    type: 'error',
                    title: 'Error inesperado',
                    text: error.message
                }));
        });
    }

    function eliminarUsuario(id, nombre) {
        // ... (Igual que antes, tu función de eliminar está bien)
        Swal.fire({
            title: '¿Eliminar usuario?',
            html: `
            <style>.swal2-popup { background-color: #1e293b !important; border-radius: 20px !important; } .swal2-title { color: #f8fafc !important; }</style>
            <div style="text-align:center;">
                <div style="background: #ef444420; width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <i class="bi bi-person-x-fill" style="color:#ef4444; font-size: 3rem;"></i>
                </div>
                <p style="color: #cbd5e1; font-size: 1.05rem;">Estás a punto de eliminar a <br><b style="color: #f8fafc;">${nombre}</b>.</p>
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
                fetch('?controlador=usuario&accion=eliminar&dive=l7', {
                        method: 'POST',
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error("Error interno.");
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
                                    title: '¡Eliminado!',
                                    text: res.msg,
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(() => {
                                    if (typeof jQuery !== 'undefined') $("#idForm").load(location.href + " #idForm > *");
                                    else window.location.reload();
                                });
                        }
                    });
            }
        });
    }
</script>