<?php
// Validamos de forma preventiva que la sesión se encuentre construida
$rol_sesion_actual = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : '';
$id_sesion_actual  = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
?>

<!-- COMPONENTE: CONCENTRACIÓN AVANZADA DE CARGA DE TRABAJO (MATRIZ CON TOTALES POR TIPO) -->
<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
        <span>📊 Carga de Trabajo: Sub-Estados y Totales por Tipo (<?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?>)</span>
        <span class="badge bg-light text-dark small shadow-sm">P: Pendiente | PR: Proceso | F: Finalizado | T: Total Tipo</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 0.78rem;">
                <thead class="table-light align-middle border-bottom">
                    <tr>
                        <th rowspan="2" class="ps-3 text-start align-middle" style="width: 20%;">Operador / Usuario</th>
                        <th rowspan="2" class="align-middle" style="width: 10%;">Nivel Rol</th>
                        <!-- AMPLIADO A COLSPAN 4 PARA INTEGRAR LA COLUMNA DE TOTAL POR TIPO -->
                        <th colspan="4" class="text-primary border-bottom border-primary-subtle py-2">📝 Actividades</th>
                        <th colspan="4" class="py-2" style="color: #6f42c1; border-bottom: 1px solid #dbcff6;">⚙️ Requerimientos</th>
                        <th colspan="4" class="text-warning-emphasis border-bottom border-warning-subtle py-2">🔎 Hallazgos</th>
                        <th rowspan="2" class="align-middle pe-3" style="width: 10%;">Balance General</th>
                    </tr>
                    <tr style="font-size: 0.7rem;" class="text-secondary fw-bold">
                        <!-- Sub-estados Actividades -->
                        <th class="py-1 bg-danger-subtle text-danger">P</th>
                        <th class="py-1 bg-warning-subtle text-warning-emphasis">PR</th>
                        <th class="py-1 bg-success-subtle text-success">F</th>
                        <th class="py-1 bg-light border-start text-dark fw-bold" style="background-color: #f1f3f5;">T</th>
                        
                        <!-- Sub-estados Requerimientos -->
                        <th class="py-1 bg-danger-subtle text-danger">P</th>
                        <th class="py-1 bg-warning-subtle text-warning-emphasis">PR</th>
                        <th class="py-1 bg-success-subtle text-success">F</th>
                        <th class="py-1 bg-light border-start text-dark fw-bold" style="background-color: #f1f3f5;">T</th>
                        
                        <!-- Sub-estados Hallazgos -->
                        <th class="py-1 bg-danger-subtle text-danger">P</th>
                        <th class="py-1 bg-warning-subtle text-warning-emphasis">PR</th>
                        <th class="py-1 bg-success-subtle text-success">F</th>
                        <th class="py-1 bg-light border-start text-dark fw-bold" style="background-color: #f1f3f5;">T</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($carga_trabajo)): ?>
                        <tr>
                            <td colspan="15" class="text-center py-4 text-muted fs-6">No se registran datos operativos de carga de trabajo para este ciclo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($carga_trabajo as $id_u => $u): ?>
                            <!-- REGLA DE PRIVACIDAD: El usuario básico solo ve su propia fila -->
                            <?php 
                            if ($rol_sesion_actual === 'basico' && $id_u !== $id_sesion_actual) {
                                continue; 
                            }
                            ?>
                            <tr class="<?php echo ($id_u === $id_sesion_actual) ? 'table-light' : ''; ?>">
                                <!-- Identificación -->
                                <td class="ps-3 text-start fw-bold text-dark">
                                    👤 <?php echo htmlspecialchars($u['nombre']); ?> 
                                    <?php echo ($id_u === $id_sesion_actual) ? '<span class="text-muted fw-normal small">(Tú)</span>' : ''; ?>
                                </td>
                                <td>
                                    <span class="badge text-capitalize bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        <?php echo htmlspecialchars($u['rol']); ?>
                                    </span>
                                </td>
                                
                                <!-- 1. Sub-Estados y TOTAL: Actividades -->
                                <td class="text-danger fw-semibold"><?php echo (int)$u['act_pendiente']; ?></td>
                                <td class="text-warning-emphasis fw-semibold"><?php echo (int)$u['act_proceso']; ?></td>
                                <td class="text-success"><?php echo (int)$u['act_finalizado']; ?></td>
                                <td class="fw-bold bg-light border-start border-end text-dark">
                                    <span class="badge bg-secondary text-white px-2 py-1 rounded-3 w-80 d-block shadow-sm">
                                             <?php echo (int)$u['act_total']; ?>   
                                        </span></td>
                                
                                <!-- 2. Sub-Estados y TOTAL: Requerimientos -->
                                <td class="text-danger fw-semibold"><?php echo (int)$u['req_pendiente']; ?></td>
                                <td class="text-warning-emphasis fw-semibold"><?php echo (int)$u['req_proceso']; ?></td>
                                <td class="text-success"><?php echo (int)$u['req_finalizado']; ?></td>
                                <td class="fw-bold  bg-light border-start border-end text-dark">  
                                    <span class="badge bg-secondary text-white px-2 py-1 rounded-3 w-80 d-block shadow-sm">
                                             <?php echo (int)$u['req_total']; ?>   
                                        </span></td>
                                
                                <!-- 3. Sub-Estados y TOTAL: Hallazgos -->
                                <td class="text-danger fw-semibold"><?php echo (int)$u['hal_pendiente']; ?></td>
                                <td class="text-warning-emphasis fw-semibold"><?php echo (int)$u['hal_proceso']; ?></td>
                                <td class="text-success"><?php echo (int)$u['hal_finalizado']; ?></td>
                                <td class="fw-bold bg-light border-start border-end text-dark">
                                    <span class="badge bg-secondary text-white px-2 py-1 rounded-3 w-80 d-block shadow-sm">
                                             <?php echo (int)$u['hal_total']; ?>  
                                        </span></td>
                                <!-- Balance General Combinado (En curso vs Concluido) -->
                                <td class="pe-3 align-middle">
                                    <div class="d-flex flex-column gap-1" style="font-size: 0.65rem;">
                                        <!-- Semáforo de Activos en Curso -->
                                        <?php if ($u['total_activo'] >= 8): ?>
                                            <span class="badge bg-danger text-white py-1 shadow-sm">🔥 <?php echo (int)$u['total_activo']; ?> En curso</span>
                                        <?php elseif ($u['total_activo'] > 0): ?>
                                            <span class="badge bg-warning text-dark py-1 shadow-sm">⚡ <?php echo (int)$u['total_activo']; ?> En curso</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white py-1 shadow-sm">👍 Libre</span>
                                        <?php endif; ?>
                                        
                                        <!-- Histórico de Concluidos en el periodo -->
                                        <?php if ($u['total_concluido'] > 0): ?>
                                            <span class="badge bg-light text-success border border-success-subtle py-1">🏁 <?php echo (int)$u['total_concluido']; ?> Cerrados</span>
                                        <?php endif; ?>
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
