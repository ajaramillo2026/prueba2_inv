<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary text-white fw-bold py-3">Nueva Tarea / Requerimiento</div>
    <div class="card-body p-3">
        <form action="index.php?accion=guardar_actividad" method="POST">
            <!-- Campo: Título -->
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Título</label>
                <input type="text" name="titulo" class="form-control form-control-sm border-secondary-subtle" required placeholder="Ej: Falla en servidor">
            </div>

            <!-- Campo: Asunto -->
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Asunto</label>
                <input type="text" name="asunto" class="form-control form-control-sm border-secondary-subtle" required placeholder="Ej: Error de conexión base de datos">
            </div>

            <!-- Campo: Descripción Ampliada -->
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Descripción Ampliada</label>
                <textarea name="descripcion" class="form-control form-control-sm border-secondary-subtle" rows="3" required placeholder="Detalla la situación o requerimiento..."></textarea>
            </div>
            
            <!-- Campos en Línea: Tipo y Medio -->
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm border-secondary-subtle" required>
                        <option value="actividad">Actividad</option>
                        <option value="requerimiento">Requerimiento</option>
                        <option value="hallazgos">Hallazgos</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Medio</label>
                    <select name="medio" class="form-select form-select-sm border-secondary-subtle" required>
                        <option value="correo">Correo</option>
                        <option value="presencial">Presencial</option>
                        <option value="llamada">Llamada</option>
                    </select>
                </div>
            </div>

            <!-- Campo: Nombre del Solicitante -->
            <div class="mb-2">
                <label class="form-label small fw-bold text-dark">Nombre del Solicitante</label>
                <input type="text" name="solicitante" class="form-control form-control-sm border-secondary-subtle" required placeholder="Ej: Ing. Carlos Mendoza">
            </div>
            
            <!-- Campos en Línea: VoBo NYA y Responsable -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">¿Lleva VoBo NYA?</label>
                    <select name="vobo_nya" class="form-select form-select-sm border-secondary-subtle">
                        <option value="no">No</option>
                        <option value="si">Sí</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold text-dark">Asignar Responsable</label>
                    <select name="usuario_id" class="form-select form-select-sm border-secondary-subtle">
                        <option value="">-- Por asignar --</option>
                        <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                            <?php foreach($usuarios as $u): ?>
                                <option value="<?php echo (int)$u['id']; ?>">
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Botón de Envío -->
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm"><i class="bi bi-floppy2"></i> Registrar</button>
        </form>
    </div>
</div>
