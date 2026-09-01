<div class="card shadow-sm border-0 rounded-3 mb-4">
    <!-- Encabezado dinámico con el mes y año evaluados en la barra de búsqueda -->
    <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
        <span>📊 Carga de Trabajo del Período Evaluado (<?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?>)</span>
        <span class="badge bg-light text-dark small shadow-sm">Métricas Mensuales</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-2" style="width: 30%;">Operador / Usuario</th>
                        <th class="py-2" style="width: 20%;">Nivel de Rol</th>
                        <th class="py-2 text-center text-warning-emphasis" style="width: 12%;">⏳ Pendientes</th>
                        <th class="py-2 text-center text-primary-emphasis" style="width: 12%;">⚙️ En Proceso</th>
                        <th class="py-2 text-center text-success-emphasis" style="width: 12%;">🏁 Finalizados</th>
                        <th class="py-2 text-center pe-3" style="width: 14%;">Carga Activa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($carga_trabajo)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted fs-6">No hay personal registrado o actividades asignadas para evaluar en este período.</td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($carga_trabajo as $id_u => $u): ?>
                            <!-- REGLA DE PRIVACIDAD: Si es básico y la fila no le pertenece, saltamos a la siguiente -->
                            <?php 
                            if ($_SESSION['usuario_tipo'] === 'basico' && $id_u != $_SESSION['usuario_id']) {
                                continue; 
                            }
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <?php echo htmlspecialchars($u['nombre']); ?> <?php echo ($id_u == $_SESSION['usuario_id']) ? '<span class="text-muted fw-normal small">(Tú)</span>' : ''; ?>
                                </td>
                                <td>
                                    <span class="badge text-capitalize bg-light text-secondary border rounded-pill px-2 py-1">
                                        <?php echo htmlspecialchars($u['rol']); ?>
                                    </span>
                                </td>
                                <td class="text-center fw-semibold text-warning fs-6"><?php echo (int)$u['pendiente']; ?></td>
                                <td class="text-center fw-semibold text-primary fs-6"><?php echo (int)$u['proceso']; ?></td>
                                <td class="text-center fw-semibold text-success fs-6"><?php echo (int)$u['finalizado']; ?></td>
                                <td class="text-center pe-3">
                                    <?php if ($u['total_activo'] >= 5): ?>
                                        <span class="badge bg-danger text-white px-2 py-1 rounded-3 w-100 d-block shadow-sm">
                                            🔥 <?php echo (int)$u['total_activo']; ?> Activas
                                        </span>
                                    <?php elseif ($u['total_activo'] > 0): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-3 w-100 d-block shadow-sm">
                                            ⚡ <?php echo (int)$u['total_activo']; ?> Activas
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success text-white px-2 py-1 rounded-3 w-100 d-block shadow-sm">
                                            👍 Disponible
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
