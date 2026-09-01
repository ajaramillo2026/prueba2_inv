<!-- PANEL DE FILTROS AVANZADOS CON SELECTOR DE MES, AÑO Y FILAS -->
<div class="card shadow-sm border-0 rounded-3 mb-3">
    <div class="card-body bg-white py-3 rounded-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="accion" value="dashboard">
            <input type="hidden" name="p" value="1"> <!-- Resetea a la página 1 al buscar -->
            
            <!-- 1. Filtro: Tipo -->
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Tipo</label>
                <select name="f_tipo" class="form-select form-select-sm border-secondary-subtle">
                    <option value="">Todos</option>
                    <option value="actividad" <?php echo (isset($_GET['f_tipo']) && $_GET['f_tipo'] == 'actividad') ? 'selected' : ''; ?>>Actividad</option>
                    <option value="requerimiento" <?php echo (isset($_GET['f_tipo']) && $_GET['f_tipo'] == 'requerimiento') ? 'selected' : ''; ?>>Requerimiento</option>
                    <option value="hallazgos" <?php echo (isset($_GET['f_tipo']) && $_GET['f_tipo'] == 'hallazgos') ? 'selected' : ''; ?>>Hallazgos</option>
                </select>
            </div>
            
            <!-- 2. Filtro: Estatus -->
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Estatus</label>
                <select name="f_status" class="form-select form-select-sm border-secondary-subtle">
                    <option value="">Todos</option>
                    <option value="por asignar" <?php echo (isset($_GET['f_status']) && $_GET['f_status'] == 'por asignar') ? 'selected' : ''; ?>>Por asignar</option>
                    <option value="pendiente" <?php echo (isset($_GET['f_status']) && $_GET['f_status'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="proceso" <?php echo (isset($_GET['f_status']) && $_GET['f_status'] == 'proceso') ? 'selected' : ''; ?>>Proceso</option>
                    <option value="finalizado" <?php echo (isset($_GET['f_status']) && $_GET['f_status'] == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                </select>
            </div>

            <!-- 3. Filtro: Mes -->
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Mes</label>
                <select name="f_mes" class="form-select form-select-sm border-secondary-subtle">
                    <?php 
                    $mes_actual_defecto = isset($_GET['f_mes']) ? (int)$_GET['f_mes'] : (int)date('n');
                    $lista_meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    foreach ($lista_meses as $num => $nombre_m): ?>
                        <option value="<?php echo $num; ?>" <?php echo ($mes_actual_defecto == $num) ? 'selected' : ''; ?>>
                            <?php echo $nombre_m; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 4. Filtro: Año -->
            <div class="col-md-1">
                <label class="form-label small fw-bold text-secondary">Año</label>
                <select name="f_anio" class="form-select form-select-sm border-secondary-subtle">
                    <?php 
                    $anio_actual_defecto = isset($_GET['f_anio']) ? (int)$_GET['f_anio'] : (int)date('Y');
                    for ($i = 2026; $i <= 2029; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($anio_actual_defecto == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- 5. NUEVO: Selector de cantidad de registros a mostrar en la barra -->
            <div class="col-md-2">
                <label class="form-label small fw-bold text-secondary">Mostrar Filas</label>
                <select name="f_limite" class="form-select form-select-sm border-secondary-subtle">
                    <option value="5" <?php echo (isset($registros_por_pagina) && $registros_por_pagina == 5) ? 'selected' : ''; ?>>5 registros</option>
                    <option value="10" <?php echo (!isset($registros_por_pagina) || $registros_por_pagina == 10) ? 'selected' : ''; ?>>10 registros</option>
                    <option value="20" <?php echo (isset($registros_por_pagina) && $registros_por_pagina == 20) ? 'selected' : ''; ?>>20 registros</option>
                    <option value="50" <?php echo (isset($registros_por_pagina) && $registros_por_pagina == 50) ? 'selected' : ''; ?>>50 registros</option>
                </select>
            </div>

            <!-- 6. Filtro: Responsable (Oculto para rol básico por privacidad) -->
            <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Responsable</label>
                    <select name="f_usuario_id" class="form-select form-select-sm border-secondary-subtle">
                        <option value="">Todos</option>
                        <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?php echo (int)$u['id']; ?>" <?php echo (isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <!-- Acciones del Formulario -->
            <div class="<?php echo ($_SESSION['usuario_tipo'] === 'basico') ? 'col-md-3' : 'col-md-1'; ?> d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold shadow-sm">Buscar</button>
                <a href="index.php?accion=exportar&f_tipo=<?php echo isset($_GET['f_tipo'])?htmlspecialchars($_GET['f_tipo']):''; ?>&f_status=<?php echo isset($_GET['f_status'])?htmlspecialchars($_GET['f_status']):''; ?>&f_usuario_id=<?php echo isset($_GET['f_usuario_id'])?htmlspecialchars($_GET['f_usuario_id']):''; ?>&f_mes=<?php echo $mes_actual_defecto; ?>&f_anio=<?php echo $anio_actual_defecto; ?>&f_limite=<?php echo isset($registros_por_pagina)?$registros_por_pagina:10; ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm" title="Exportar a Excel">📊</a>
            </div>
        </form>
    </div>
</div>

<!-- (Abajo de este bloque continúa la estructura completa del <table> con las filas del personal que ya habías ensamblado) -->


<!-- TABLA PRINCIPAL DE DATOS -->
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light border-bottom">
                    <tr>
                        <!--<th class="ps-3 py-3 text-secondary text-uppercase" style="width: 5%;">ID</th>-->
                        <th class="py-3 text-secondary text-uppercase" style="width: 25%;">Título y Asunto</th>
                        <th class="py-3 text-secondary text-uppercase" style="width: 15%;">Clasificación</th>
                        <th class="py-3 text-secondary text-uppercase" style="width: 15%;">Solicitante</th>
                        <th class="text-center py-3 text-secondary text-uppercase" style="width: 8%;">VoBo</th>
                        <!-- NUEVO: Columna dinámica basada en el rol -->
                        <?php if ($_SESSION['usuario_tipo'] === 'basico'): ?>
                            <th class="py-3 text-secondary text-uppercase" style="width: 15%;">Asignado A</th>
                            <th class="py-3 text-secondary text-uppercase" style="width: 17%; min-width: 150px;">Estatus</th>
                        <?php else: ?>
                            <th class="py-3 text-secondary text-uppercase" style="width: 32%; min-width: 290px;">Estatus y Responsable</th>
                            <th class="py-3 text-secondary text-uppercase" style="width: 8%; min-width: 60px;">Acción</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($actividades)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted fs-6">Ninguna actividad coincide con los criterios de búsqueda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($actividades as $act): ?>
                            <tr class="<?php echo ($act['status'] == 'finalizado') ? 'table-success bg-success-subtle bg-opacity-25' : ''; ?>">
                               <!-- <td class="fw-bold ps-3 text-secondary"><?php// echo (int)$act['id']; ?></td>-->
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($act['titulo']); ?></div>
                                    <div class="text-muted text-truncate" style="max-width: 230px;" title="<?php echo htmlspecialchars($act['descripcion']); ?>">
                                        🔎 <?php echo htmlspecialchars($act['asunto']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-dark bg-light border text-capitalize"><?php echo htmlspecialchars($act['tipo']); ?></span>
                                    <div class="text-muted small mt-1">📥 <?php echo htmlspecialchars($act['medio']); ?></div>
                                </td>
                                <td class="fw-semibold text-secondary"><?php echo htmlspecialchars($act['solicitante']); ?></td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($act['vobo_nya'] == 'si') ? 'text-success bg-success-subtle border border-success-subtle' : 'text-secondary bg-light border'; ?> px-2 py-1">
                                        <?php echo strtoupper($act['vobo_nya']); ?>
                                    </span>
                                </td>

                                <!-- VISTA PARA EL ROL BÁSICO (Texto estático + select simple) -->
                                <?php if ($_SESSION['usuario_tipo'] === 'basico'): ?>
                                    <td class="fw-bold text-primary">
                                        👤 <?php echo htmlspecialchars($act['encargado'] ?? 'Sin Asignar'); ?>
                                    </td>
                                    <td>
                                        <form action="index.php?accion=actualizar_actividad" method="POST" class="d-flex gap-1 m-0">
                                            <input type="hidden" name="id" value="<?php echo (int)$act['id']; ?>">
                                            <select name="status" class="form-select form-select-sm text-capitalize border-secondary-subtle">
                                                <option value="por asignar" <?php echo ($act['status'] == 'por asignar') ? 'selected' : ''; ?>>Por asignar</option>
                                                <option value="pendiente" <?php echo ($act['status'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="proceso" <?php echo ($act['status'] == 'proceso') ? 'selected' : ''; ?>>Proceso</option>
                                                <option value="finalizado" <?php echo ($act['status'] == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Guardar Cambios"><i class="bi bi-floppy2"></i></button>
                                        </form>
                                        <?php if ($act['status'] == 'finalizado' && !empty($act['fecha_finalizacion'])): ?>
                                            <div class="mt-1 small text-success" style="font-size: 0.7rem;">
                                                🏁 Concluido: <?php echo date('d/m H:i', strtotime($act['fecha_finalizacion'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                <?php else: ?>
                                    <!-- Bloque para Administradores e Intermedios (Selects cruzados) -->
                                    <td>
                                        <form action="index.php?accion=actualizar_actividad" method="POST" class="row g-1 align-items-center m-0">
                                            <input type="hidden" name="id" value="<?php echo (int)$act['id']; ?>">
                                            
                                            <div class="col-4">
                                                <select name="status" class="form-select form-select-sm text-capitalize border-secondary-subtle">
                                                    <option value="por asignar" <?php echo ($act['status'] == 'por asignar') ? 'selected' : ''; ?>>Por asignar</option>
                                                    <option value="pendiente" <?php echo ($act['status'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="proceso" <?php echo ($act['status'] == 'proceso') ? 'selected' : ''; ?>>Proceso</option>
                                                    <option value="finalizado" <?php echo ($act['status'] == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-6">
                                                <select name="usuario_id" class="form-select form-select-sm border-secondary-subtle">
                                                    <option value="">-- Sin Asignar --</option>
                                                    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                                                        <?php foreach ($usuarios as $u): ?>
                                                            <option value="<?php echo (int)$u['id']; ?>" <?php echo ($act['usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($u['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="col-2">
                                                <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Guardar Cambios"><i class="bi bi-floppy2"></i></button>
                                            </div>
                                            <!-- la col-2 del Administrador-->
                                            <div class="col-2 d-flex gap-1">
                                            <td>
                                                <?php if ($_SESSION['usuario_tipo'] === 'administrador'): ?>
                                                    <!-- Botón Modal: Pasa los datos de la fila mediante atributos 'data-bs-*' -->
                                                    <button type="button" class="btn btn-sm btn-warning shadow-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalEditarActividad"
                                                            data-id="<?php echo $act['id']; ?>"
                                                            data-titulo="<?php echo htmlspecialchars($act['titulo']); ?>"
                                                            data-asunto="<?php echo htmlspecialchars($act['asunto']); ?>"
                                                            data-descripcion="<?php echo htmlspecialchars($act['descripcion']); ?>"
                                                            data-tipo="<?php echo $act['tipo']; ?>"
                                                            data-medio="<?php echo $act['medio']; ?>"
                                                            data-solicitante="<?php echo htmlspecialchars($act['solicitante']); ?>"
                                                            data-vobo="<?php echo $act['vobo_nya']; ?>"
                                                            title="Editar Contenido"><i class="bi bi-pencil-square"></i></button>

                                                    <a href="index.php?accion=eliminar_actividad&id=<?php echo (int)$act['id']; ?>" 
                                                    class="btn btn-sm btn-danger shadow-sm" title="Eliminar Tarea" 
                                                    onclick="return confirm('¿Seguro?')"><i class="bi bi-trash"></i></a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-light border w-100" disabled title="No tienes permisos">🚫</button>
                                                <?php endif; ?>
                                                </td>
                                            </div>

                                        </form>
      
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
 <!-- BARRA INFERIOR DE PAGINACIÓN ADAPTADA A FILTROS CRUZAODS -->
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 rounded-bottom-3">
        <div class="text-muted small">
            Mostrando página <strong><?php echo $pagina_actual; ?></strong> de <strong><?php echo $total_paginas; ?></strong> (Total: <?php echo $total_registros; ?> registros)
        </div>
        <nav>
            <ul class="pagination pagination-sm m-0 shadow-sm">
                <!-- Botón: Anterior -->
                <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="index.php?accion=dashboard&p=<?php echo $pagina_actual - 1; ?>&f_tipo=<?php echo $_GET['f_tipo']??''; ?>&f_status=<?php echo $_GET['f_status']??''; ?>&f_usuario_id=<?php echo $_GET['f_usuario_id']??''; ?>&f_mes=<?php echo $mes_actual_defecto; ?>&f_anio=<?php echo $anio_actual_defecto; ?>&f_limite=<?php echo $registros_por_pagina; ?>">Anterior</a>
                </li>
                
                <!-- Botón: Siguiente -->
                <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="index.php?accion=dashboard&p=<?php echo $pagina_actual + 1; ?>&f_tipo=<?php echo $_GET['f_tipo']??''; ?>&f_status=<?php echo $_GET['f_status']??''; ?>&f_usuario_id=<?php echo $_GET['f_usuario_id']??''; ?>&f_mes=<?php echo $mes_actual_defecto; ?>&f_anio=<?php echo $anio_actual_defecto; ?>&f_limite=<?php echo $registros_por_pagina; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

<!-- VENTANA MODAL PARA EDITAR ACTIVIDAD (SOLO ADMINISTRADOR) -->
<div class="modal fade" id="modalEditarActividad" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalTituloLabel">✏️ Editar Contenido de Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?accion=editar_contenido_actividad" method="POST">
                <div class="modal-body p-4">
                    <!-- ID Oculto para la consulta SQL -->
                    <input type="hidden" name="id" id="edit-id">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título</label>
                        <input type="text" name="titulo" id="edit-titulo" class="form-control" Required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Asunto</label>
                        <input type="text" name="asunto" id="edit-asunto" class="form-control" Required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Descripción Ampliada</label>
                        <textarea name="descripcion" id="edit-descripcion" class="form-control" rows="3" Required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select name="tipo" id="edit-tipo" class="form-select" Required>
                                <option value="actividad">Actividad</option>
                                <option value="requerimiento">Requerimiento</option>
                                <option value="hallazgos">Hallazgos</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Medio</label>
                            <select name="medio" id="edit-medio" class="form-select" Required>
                                <option value="correo">Correo</option>
                                <option value="presencial">Presencial</option>
                                <option value="llamada">Llamada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Solicitante</label>
                            <input type="text" name="solicitante" id="edit-solicitante" class="form-control" Required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">¿Lleva VoBo NYA?</label>
                            <select name="vobo_nya" id="edit-vobo" class="form-select">
                                <option value="no">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold shadow-sm">Actualizar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT JS PARA PASAR DATOS AL MODAL DINÁMICAMENTE -->
<script>
const modalEditar = document.getElementById('modalEditarActividad');
if (modalEditar) {
    modalEditar.addEventListener('show.bs.modal', event => {
        // Botón que disparó el modal
        const boton = event.relatedTarget;
        
        // Extraemos la información de los atributos data-bs-*
        document.getElementById('edit-id').value = boton.getAttribute('data-id');
        document.getElementById('edit-titulo').value = boton.getAttribute('data-titulo');
        document.getElementById('edit-asunto').value = boton.getAttribute('data-asunto');
        document.getElementById('edit-descripcion').value = boton.getAttribute('data-descripcion');
        document.getElementById('edit-tipo').value = boton.getAttribute('data-tipo');
        document.getElementById('edit-medio').value = boton.getAttribute('data-medio');
        document.getElementById('edit-solicitante').value = boton.getAttribute('data-solicitante');
        document.getElementById('edit-vobo').value = boton.getAttribute('data-vobo');
    });
}
</script>


</div>
</div>
