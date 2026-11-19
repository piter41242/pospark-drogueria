<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>
<div class="row mb-3">
    <div class="col-12">
        <h3 class="fw-bold" style="color:#146c43;"><i class="bi bi-pencil-square"></i> Editar Sede</h3>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="?controlador=sede&accion=editar&id=<?php echo $sede['idSede']; ?>&dive=l4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Negocio *</label>
                    <select name="idNegocio" class="form-select" required>
                        <?php foreach ($negocios as $n): ?>
                            <option value="<?php echo $n['idNegocio']; ?>"
                                <?php echo ($n['idNegocio'] == $sede['idNegocio']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($n['razon_social']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de la sede *</label>
                    <input type="text" name="nombre" class="form-control" maxlength="100" required
                           value="<?php echo htmlspecialchars($sede['nombre']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dirección *</label>
                    <input type="text" name="direccion" class="form-control" maxlength="150" required
                           value="<?php echo htmlspecialchars($sede['direccion']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="25"
                           value="<?php echo htmlspecialchars($sede['telefono']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" maxlength="80"
                           value="<?php echo htmlspecialchars($sede['ciudad']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Código DANE</label>
                    <input type="text" name="codigo_dane" class="form-control" maxlength="10"
                           value="<?php echo htmlspecialchars($sede['codigo_dane'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Estado *</label>
                    <select name="estado" class="form-select" required>
                        <option value="1" <?php echo ($sede['estado'] == 1) ? 'selected' : ''; ?>>Activa</option>
                        <option value="2" <?php echo ($sede['estado'] == 2) ? 'selected' : ''; ?>>Inactiva / Bloqueada</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="?controlador=sede&accion=inicio&dive=l4" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Actualizar Sede
                </button>
            </div>
        </form>
    </div>
</div>