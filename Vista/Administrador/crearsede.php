<?php
if (!isset($_SESSION['usuario']['idUsuario'])) { header("Location:../../index.php"); exit(); }
?>
<div class="row mb-3">
    <div class="col-12">
        <h3 class="fw-bold" style="color:#146c43;"><i class="bi bi-plus-circle"></i> Nueva Sede</h3>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="?controlador=sede&accion=crear&dive=l4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Negocio *</label>
                    <select name="idNegocio" class="form-select" required>
                        <?php foreach ($negocios as $n): ?>
                            <option value="<?php echo $n['idNegocio']; ?>">
                                <?php echo htmlspecialchars($n['razon_social']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de la sede *</label>
                    <input type="text" name="nombre" class="form-control" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dirección *</label>
                    <input type="text" name="direccion" class="form-control" maxlength="150" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="25">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" maxlength="80">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Código DANE</label>
                    <input type="text" name="codigo_dane" class="form-control" maxlength="10"
                           placeholder="Ej: 11001000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Estado *</label>
                    <select name="estado" class="form-select" required>
                        <option value="1" selected>Activa</option>
                        <option value="2">Inactiva / Bloqueada</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="?controlador=sede&accion=inicio&dive=l4" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Guardar Sede
                </button>
            </div>
        </form>
    </div>
</div>