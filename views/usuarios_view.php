<?php require_once 'views/partials/header.php'; ?>
<?php require_once 'views/partials/navbar.php'; ?>

<div class="container-fluid px-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-secondary fw-bold m-0 fs-3">Administración de Usuarios y Roles</h2>
    </div>

    <div class="row g-4">
        <!-- FORMULARIO (CREAR / EDITAR) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <?php echo $usuarioEditar ? '✏️ Editar Usuario Registrado' : '➕ Registrar Nuevo Usuario'; ?>
                </div>
                <div class="card-body p-4">
                    <form action="index.php?accion=guardar_usuario" method="POST">
                        <input type="hidden" name="id" value="<?php echo $usuarioEditar['id'] ?? ''; ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nombre de Acceso (Usuario ID)</label>
                            <input type="text" name="usuario" class="form-control" 
                                   value="<?php echo $usuarioEditar['usuario'] ?? ''; ?>" placeholder="Ej: jgarcia" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nombre Real Completo</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?php echo $usuarioEditar['nombre'] ?? ''; ?>" placeholder="Ej: Juan García" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Tipo de Permiso / Rol</label>
                            <select name="tipo_usuario" class="form-select" required>
                                <option value="" disabled <?php echo !$usuarioEditar ? 'selected' : ''; ?>>Seleccione un rol...</option>
                                <option value="administrador" <?php echo (isset($usuarioEditar) && $usuarioEditar['tipo_usuario'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="intermedio" <?php echo (isset($usuarioEditar) && $usuarioEditar['tipo_usuario'] == 'intermedio') ? 'selected' : ''; ?>>Intermedio</option>
                                <option value="basico" <?php echo (isset($usuarioEditar) && $usuarioEditar['tipo_usuario'] == 'basico') ? 'selected' : ''; ?>>Básico</option>
                            </select>
                        </div>

                        <?php if (!$usuarioEditar): ?>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">Contraseña de Ingreso</label>
                                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 small text-dark mb-4 bg-warning-subtle">
                                ℹ️ Por seguridad, la contraseña no se puede modificar desde este módulo.
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2"><i class="bi bi-floppy2"></i></button>
                            <?php if ($usuarioEditar): ?>
                                <a href="index.php?accion=usuarios" class="btn btn-light btn-sm border py-2">Cancelar Edición</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABLA DE REGISTROS -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-dark text-white fw-bold py-3">Personal Registrado en Sistema</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <!--<th class="ps-4 py-3 text-secondary small text-uppercase">ID</th>-->
                                    <th class="py-3 text-secondary small text-uppercase">Usuario Acceso</th>
                                    <th class="py-3 text-secondary small text-uppercase">Nombre Real</th>
                                    <th class="py-3 text-secondary small text-uppercase">Nivel de Rol</th>
                                    <th class="text-center py-3 text-secondary small text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuariosAdmin)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuariosAdmin as $u): ?>
                                        <tr>
                                            <!--<td class="ps-4 fw-bold text-secondary"><?php// echo $u['id']; ?></td>-->
                                            <td class="fw-semibold text-primary"><?php echo htmlspecialchars($u['usuario']); ?></td>
                                            <td class="text-dark"><?php echo htmlspecialchars($u['nombre']); ?></td>
                                            <td>
                                                <?php if($u['tipo_usuario'] == 'administrador'): ?>
                                                    <span class="badge text-danger bg-danger-subtle px-2 py-1 border border-danger-subtle rounded-pill">Administrador</span>
                                                <?php elseif($u['tipo_usuario'] == 'intermedio'): ?>
                                                    <span class="badge text-warning bg-warning-subtle px-2 py-1 border border-warning-subtle rounded-pill">Intermedio</span>
                                                <?php else: ?>
                                                    <span class="badge text-info bg-info-subtle px-2 py-1 border border-info-subtle rounded-pill">Básico</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="index.php?accion=usuarios&editar_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="index.php?accion=eliminar_usuario&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')"><i class="bi bi-trash"></i></a></a>
                                                    <button type="button" class="btn btn-sm btn-info shadow-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCambiarPassword" 
                                                        data-id="<?php echo $u['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                                                        title="Cambiar Contraseña"><i class="bi bi-key-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL: CAMBIO DE CONTRASEÑA (SOLO ADMINISTRADOR) -->
<div class="modal fade" id="modalCambiarPassword" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 22rem;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6">🔑 Modificar Contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?accion=cambiar_password_usuario" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_usuario_pass" id="pass-id">
                    
                    <div class="mb-2 small text-muted">
                        Personal: <strong id="pass-nombre" class="text-dark"></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nueva Contraseña</label>
                        <input type="password" name="nueva_password" class="form-control form-control-sm border-secondary-subtle" minlength="6" placeholder="Escribe la nueva clave..." required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Regresar</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm text-dark">Cambiar <i class="bi bi-key-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT JS: Pasa el ID y Nombre al Modal automáticamente -->
<script>
const modalPass = document.getElementById('modalCambiarPassword');
if (modalPass) {
    modalPass.addEventListener('show.bs.modal', event => {
        const boton = event.relatedTarget;
        document.getElementById('pass-id').value = boton.getAttribute('data-id');
        document.getElementById('pass-nombre').innerText = boton.getAttribute('data-nombre');
    });
}
</script>


<?php require_once 'views/partials/footer.php'; ?>