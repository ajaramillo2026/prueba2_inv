<?php 
// 1. Cargamos de forma modular la cabecera HTML y la barra de navegación comunes
require_once 'views/partials/header.php'; 
require_once 'views/partials/navbar.php'; 
?>

<div class="container-fluid px-4">
    <h2 class="mb-4 text-secondary fw-bold fs-3">Gestión de Tickets Relacionales</h2>
    
    <!-- FILA DE CONTADORES EN TIEMPO REAL (KPIs DE TICKETS) -->
    <div class="row g-3 mb-4">
        <!-- Tarjeta 1: Día -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-danger bg-danger-subtle bg-opacity-25 rounded-3">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-danger-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem;">Tickets del Día</div>
                        <h3 class="fw-bold text-dark m-0 fs-1" id="t-kpi-hoy"><?php echo (int)($kpis_iniciales['hoy'] ?? 0); ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">🚨</div>
                </div>
            </div>
        </div>
        <!-- Tarjeta 2: Últimos 7 Días -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning bg-warning-subtle bg-opacity-25 rounded-3">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-warning-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem;">Últimos 7 Días</div>
                        <h3 class="fw-bold text-dark m-0 fs-1" id="t-kpi-semana"><?php echo (int)($kpis_iniciales['semana'] ?? 0); ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">📅</div>
                </div>
            </div>
        </div>
        <!-- Tarjeta 3: Acumulado del Mes -->
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary bg-primary-subtle bg-opacity-25 rounded-3">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-primary-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem;">Acumulado del Mes</div>
                        <h3 class="fw-bold text-dark m-0 fs-1" id="t-kpi-mes"><?php echo (int)($kpis_iniciales['mes'] ?? 0); ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">📊</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CUERPO PRINCIPAL DEL PANEL EN REJILLA RESPONSIVA -->
    <div class="row g-4">
        
        <!-- CORREGIDO: Validación estricta por sesión de PHP para mostrar el formulario al Administrador e Intermedio -->
        <?php if ($_SESSION['usuario_tipo'] === 'administrador' || $_SESSION['usuario_tipo'] === 'intermedio'): ?>
            <!-- COLUMNA IZQUIERDA: FORMULARIO LATERAL DE CAPTURA -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        <?php echo isset($ticketEditar) && $ticketEditar ? '✏️ Editar Ticket' : '🎫 Asignar Nuevo Ticket'; ?>
                    </div>
                    <div class="card-body p-4">
                        <form action="index.php?accion=guardar_ticket" method="POST">
                            <input type="hidden" name="id" value="<?php echo $ticketEditar['id'] ?? ''; ?>">
                            
                            <!-- Selector de Usuario Solicitante -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Usuario Relacionado</label>
                                <select name="usuario_id" class="form-select border-secondary-subtle" required>
                                    <option value="" disabled <?php echo !isset($ticketEditar) ? 'selected' : ''; ?>>Seleccione un usuario...</option>
                                    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo $u['id']; ?>" <?php echo (isset($ticketEditar) && $ticketEditar['usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($u['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Selector de Clasificación (Catálogo Dinámico) -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Tipo de Ticket</label>
                                <select name="tipo_ticket_id" class="form-select border-secondary-subtle" required>
                                    <option value="" disabled <?php echo !isset($ticketEditar) ? 'selected' : ''; ?>>Seleccione tipo...</option>
                                    <?php if (!empty($tipos_catalogo) && is_array($tipos_catalogo)): ?>
                                        <?php foreach ($tipos_catalogo as $tc): ?>
                                            <option value="<?php echo $tc['id']; ?>" <?php echo (isset($ticketEditar) && $ticketEditar['tipo_ticket_id'] == $tc['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tc['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Campo de Extensión Obligatorio de 4 dígitos -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">Extensión Telefónica (4 dígitos)</label>
                                <input type="text" name="extension" class="form-control border-secondary-subtle" 
                                       value="<?php echo $ticketEditar['extension'] ?? ''; ?>" 
                                       placeholder="Ej: 4502" required maxlength="4" pattern="[0-9]{4}" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm"><i class="bi bi-floppy2"></i> Enviar</button>
                                <?php if (isset($ticketEditar) && $ticketEditar): ?>
                                    <a href="index.php?accion=tickets" class="btn btn-light btn-sm border py-2">Cancelar Edición</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- COLUMNA DERECHA: Carga de Trabajo (Arriba), Filtros Avanzados (Medio) e Historial (Abajo) -->
        <div class="<?php echo ($_SESSION['usuario_tipo'] === 'administrador' || $_SESSION['usuario_tipo'] === 'intermedio') ? 'col-xl-8 col-lg-7' : 'col-12'; ?>">
            
            <!-- COMPONENTE A: CARGA DE TICKETS POR PERSONAL -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>📊 Carga de Tickets(Filtros Activos)</span>
                    <span class="badge bg-light text-dark small shadow-sm">Volumen de Búsqueda</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2" style="width: 35%;">Personal</th>
                                    <!--<th class="py-2" style="width: 20%;">Nivel de Rol</th>-->
                                    <th class="py-2 text-center text-danger-emphasis" style="width: 15%;">🚨 Día</th>
                                    <th class="py-2 text-center text-warning-emphasis" style="width: 15%;">📅 Semana</th>
                                    <th class="py-2 text-center text-primary-emphasis" style="width: 15%;">📊 Mes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($carga_tickets)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No se registran datos.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($carga_tickets as $id_u_t => $ut): ?>
                                        <?php if ($_SESSION['usuario_tipo'] === 'basico' && $id_u_t != $_SESSION['usuario_id']) continue; ?>
                                        <tr class="<?php echo ($id_u_t == $_SESSION['usuario_id']) ? 'table-light' : ''; ?>">
                                            <td class="ps-3 fw-bold text-dark">
                                                👤 <?php echo htmlspecialchars($ut['nombre']); ?> 
                                                <?php echo ($id_u_t == $_SESSION['usuario_id']) ? '<span class="text-muted fw-normal small">(Tú)</span>' : ''; ?>
                                            </td>
                                            <!--<td>
                                                <span class="badge text-capitalize bg-light text-secondary border rounded-pill px-2 py-1">
                                                    <?php // echo htmlspecialchars($ut['rol']); ?>
                                                </span>
                                            </td>-->
                                            <td class="text-center fw-bold text-danger fs-6"><?php echo (int)$ut['hoy']; ?></td>
                                            <td class="text-center fw-bold text-warning fs-6"><?php echo (int)$ut['semana']; ?></td>
                                            <td class="text-center fw-bold text-primary fs-6"><?php echo (int)$ut['mes']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- COMPONENTE B: BARRA DE FILTROS REUBICADA ENTRE AMBAS TABLAS CON PAGINACIÓN -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body bg-white py-3 rounded-3">
                    <form method="GET" action="index.php" class="row g-2 align-items-end">
                        <input type="hidden" name="accion" value="tickets">
                        <input type="hidden" name="p" value="1"> <!-- Resetea a la página 1 al buscar -->
                        
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Filtrar por Tipo</label>
                            <select name="f_tipo_ticket_id" class="form-select form-select-sm border-secondary-subtle">
                                <option value="">Todos los Tipos</option>
                                <?php if (!empty($tipos_catalogo) && is_array($tipos_catalogo)): ?>
                                    <?php foreach ($tipos_catalogo as $tc): ?>
                                        <option value="<?php echo $tc['id']; ?>" <?php echo (isset($_GET['f_tipo_ticket_id']) && $_GET['f_tipo_ticket_id'] == $tc['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tc['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Filtrar por Mes</label>
                            <select name="f_mes" class="form-select form-select-sm border-secondary-subtle">
                                <?php 
                                $mes_def = isset($_GET['f_mes']) ? (int)$_GET['f_mes'] : (int)date('n');
                                $list_m = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                                foreach ($list_m as $n => $nm): ?>
                                    <option value="<?php echo $n; ?>" <?php echo ($mes_def == $n) ? 'selected' : ''; ?>><?php echo $nm; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Buscar Extensión</label>
                            <input type="text" name="f_extension" class="form-control form-control-sm border-secondary-subtle py-1.5" 
                                   value="<?php echo htmlspecialchars($_GET['f_extension'] ?? ''); ?>" 
                                   placeholder="Ej: 4502" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <!-- NUEVO: Selector de cantidad de registros a mostrar -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-secondary">Mostrar filas</label>
                            <select name="f_limite" class="form-select form-select-sm border-secondary-subtle">
                                <option value="5" <?php echo ($registros_por_pagina == 5) ? 'selected' : ''; ?>>5 filas</option>
                                <option value="10" <?php echo ($registros_por_pagina == 10) ? 'selected' : ''; ?>>10 filas</option>
                                <option value="20" <?php echo ($registros_por_pagina == 20) ? 'selected' : ''; ?>>20 filas</option>
                            </select>
                        </div>

                        <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-secondary">Filtrar Usuario</label>
                                <select name="f_usuario_id" class="form-select form-select-sm border-secondary-subtle">
                                    <option value="">Todos los Usuarios</option>
                                    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo $u['id']; ?>" <?php echo (isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($u['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="col d-grid">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold py-1.5 shadow-sm">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- COMPONENTE C: HISTORIAL DE CLASIFICACIONES CON INTERFAZ DE PAGINACIÓN -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-dark text-white fw-bold py-3">Historial de Clasificaciones</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="ps-3 py-3 text-secondary text-uppercase" style="width: 35%;">Usuario Relacionado</th>
                                    <th class="py-3 text-secondary text-uppercase" style="width: 25%;">Clasificación Ticket</th>
                                    <th class="py-3 text-secondary text-uppercase" style="width: 25%;">Extensión</th>
                                    <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                                        <th class="text-center py-3 text-secondary text-uppercase" style="width: 15%;">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tickets)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted fs-6">No se registran tickets en el período.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tickets as $t): ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark">👤 <?php echo htmlspecialchars($t['cliente']); ?></td>
                                            <td>
                                                <?php 
                                                $clase_badge = 'bg-light text-dark';
                                                if ($t['tipo_nombre'] === 'ITQ CPU') $clase_badge = 'text-primary bg-primary-subtle border-primary-subtle';
                                                if ($t['tipo_nombre'] === 'ITQ ACTIVIDAD') $clase_badge = 'text-purple bg-purple-subtle border-purple-subtle';
                                                if ($t['tipo_nombre'] === 'ITQ CORREO') $clase_badge = 'text-info bg-info-subtle border-info-subtle';
                                                ?>
                                                <span class="badge border px-3 py-1 rounded-pill <?php echo $clase_badge; ?>" style="<?php echo $t['tipo_nombre'] === 'ITQ ACTIVIDAD' ? 'color: #6f42c1; background-color: #efebfc; border-color: #dbcff6;' : ''; ?>">
                                                    <?php echo htmlspecialchars($t['tipo_nombre']); ?>
                                                </span>
                                            </td>
                                            <td class="text-secondary fw-bold">
                                                📞 Ext. <?php echo htmlspecialchars($t['extension']); ?>
                                            </td>
                                            <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="index.php?accion=tickets&editar_id=<?php echo (int)$t['id']; ?>" class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-square"></i></a>
                                                        <a href="index.php?accion=eliminar_ticket&id=<?php echo (int)$t['id']; ?>" class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('¿Deseas eliminar este registro?')"><i class="bi bi-trash"></i></a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- NUEVO: BARRA INFERIOR CON LOS BOTONES DE PAGINACIÓN DE TICKETS -->
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 rounded-bottom-3">
                    <div class="text-muted small">
                        Página <strong><?php echo $pagina_actual; ?></strong> de <strong><?php echo $total_paginas_tickets; ?></strong> (Total: <?php echo $total_registros_tickets; ?> registros)
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm m-0 shadow-sm">
                            <!-- Botón: Anterior -->
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?accion=tickets&p=<?php echo $pagina_actual - 1; ?>&f_tipo_ticket_id=<?php echo $_GET['f_tipo_ticket_id']??''; ?>&f_mes=<?php echo $mes_def; ?>&f_extension=<?php echo $_GET['f_extension']??''; ?>&f_usuario_id=<?php echo $_GET['f_usuario_id']??''; ?>&f_limite=<?php echo $registros_por_pagina; ?>">Anterior</a>
                            </li>
                            <!-- Botón: Siguiente -->
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas_tickets) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?accion=tickets&p=<?php echo $pagina_actual + 1; ?>&f_tipo_ticket_id=<?php echo $_GET['f_tipo_ticket_id']??''; ?>&f_mes=<?php echo $mes_def; ?>&f_extension=<?php echo $_GET['f_extension']??''; ?>&f_usuario_id=<?php echo $_GET['f_usuario_id']??''; ?>&f_limite=<?php echo $registros_por_pagina; ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div> <!-- Fin Card Historial -->

        </div> <!-- Fin col-8 / col-12 -->
    </div> <!-- Fin row general grid -->
</div> <!-- Fin container-fluid -->

<!-- SCRIPT ASÍNCRONO AUTOMÁTICO EN TIEMPO REAL -->
<script>
function sincronizarContadoresTickets() {
    fetch('index.php?accion=get_tickets_ajax')
        .then(response => {
            if (!response.ok) throw new Error('Error en API de Tickets');
            return response.json();
        })
        .then(data => {
            if (document.getElementById('t-kpi-hoy')) {
                document.getElementById('t-kpi-hoy').innerText    = data.hoy;
                document.getElementById('t-kpi-semana').innerText = data.semana;
                document.getElementById('t-kpi-mes').innerText    = data.mes;
            }
        })
        .catch(error => console.error('Fallo en sincronización asíncrona:', error));
}
// Carga inmediata inicial
sincronizarContadoresTickets();
// Repetición cíclica continua en segundo plano cada 3 segundos exactos
setInterval(sincronizarContadoresTickets, 3000);
</script>

<?php 
// 3. Cargamos el pie de página que cierra de forma ordenada las etiquetas HTML y los scripts comunes
require_once 'views/partials/footer.php'; 
?>
