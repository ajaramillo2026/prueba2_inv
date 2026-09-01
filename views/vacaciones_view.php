<?php 
require_once 'views/partials/header.php'; 
require_once 'views/partials/navbar.php'; 
?>

<div class="container-fluid px-4">
    <h2 class="mb-4 text-secondary fw-bold fs-3">Control de Vacaciones (Ley de Vacaciones Dignas LFT)</h2>

    <!-- SECCIÓN DE RETROALIMENTACIÓN DE ERRORES O CANDADOS -->
    <?php if (isset($_SESSION['error_vacaciones'])): ?>
        <div class="alert alert-danger border-0 shadow-sm text-center small mb-3"><?php echo $_SESSION['error_vacaciones']; unset($_SESSION['error_vacaciones']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['exito_vacaciones'])): ?>
        <div class="alert alert-success border-0 shadow-sm text-center small mb-3"><?php echo $_SESSION['exito_vacaciones']; unset($_SESSION['exito_vacaciones']); ?></div>
    <?php endif; ?>

    <!-- TARJETAS DE EXPEDIENTE PERSONAL (Visible para todos respecto a su cuenta) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary bg-primary-subtle bg-opacity-10 rounded-3 p-3">
                <div class="small fw-bold text-uppercase text-primary mb-1" style="font-size:0.7rem;">Mis Días por Ley</div>
                <h3 class="fw-bold text-dark m-0 fs-2"><?php echo $mi_expediente['ley']; ?> <span class="fs-6 fw-normal text-muted">días (Aniversario #<?php echo $mi_expediente['anios']; ?>)</span></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-secondary bg-secondary-subtle bg-opacity-10 rounded-3 p-3">
                <div class="small fw-bold text-uppercase text-secondary mb-1" style="font-size:0.7rem;">Mis Días Consumidos / Apartados</div>
                <h3 class="fw-bold text-dark m-0 fs-2"><?php echo $mi_expediente['consumidos']; ?> <span class="fs-6 fw-normal text-muted">días</span></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-success bg-success-subtle bg-opacity-10 rounded-3 p-3">
                <div class="small fw-bold text-uppercase text-success mb-1" style="font-size:0.7rem;">Mis Días Disponibles Actuales</div>
                <h3 class="fw-bold text-dark m-0 fs-2"><?php echo $mi_expediente['disponibles']; ?> <span class="fs-6 fw-normal text-muted">días netos</span></h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($_SESSION['usuario_tipo'] === 'administrador' || $_SESSION['usuario_tipo'] === 'intermedio'): ?>
            <!-- FORMULARIO LATERAL CON CANDADO EN TIEMPO REAL -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white fw-bold py-3">🎫 Registrar Período</div>
                    <div class="card-body p-4">
                        <form action="index.php?accion=guardar_vacacion" method="POST" id="formVacaciones">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Colaborador</label>
                                <select name="usuario_id" id="selectUsuarioVac" class="form-select border-secondary-subtle" required>
                                    <option value="" disabled selected>Seleccione un empleado...</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?php echo $u['id']; ?>" data-disponibles="<?php echo $u['dias_disponibles']; ?>">
                                            <?php echo htmlspecialchars($u['nombre']); ?> (Disponibles: <?php echo $u['dias_disponibles']; ?>d)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_ini" class="form-control border-secondary-subtle" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control border-secondary-subtle" required>
                                </div>
                            </div>

                            <!-- Alerta de Bloqueo Dinámico -->
                            <div id="alertaExcesoDias" class="alert alert-danger border-0 small d-none py-2 mb-3">
                                🚫 Error: Los días solicitados superan el saldo disponible del usuario.
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Estatus Resolución</label>
                                <select name="estatus" class="form-select border-secondary-subtle" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="aprobado">Aprobado</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">Observaciones</label>
                                <textarea name="observaciones" class="form-control border-secondary-subtle" rows="2" placeholder="Detalles corporativos..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="btnGuardarVac" class="btn btn-success fw-bold py-2 shadow-sm"><i class="bi bi-floppy2"></i> Registrar Vacaciones</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- CUADRÍCULA DEL HISTORIAL GENERAL -->
        <div class="<?php echo ($_SESSION['usuario_tipo'] === 'administrador' || $_SESSION['usuario_tipo'] === 'intermedio') ? 'col-xl-8 col-lg-7' : 'col-12'; ?>">
            <div class="card shadow-sm border-0 rounded-3 card-body p-4 bg-white">
                <div class="table-responsive">
                    <table id="tablaVacaciones" class="table table-hover align-middle mb-0 small w-100">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-3 py-3 text-secondary text-uppercase">Colaborador</th>
                                <th class="py-3 text-secondary text-uppercase text-center">Período de Descanso</th>
                                <th class="py-3 text-secondary text-uppercase text-center">Días Calendario</th>
                                <th class="py-3 text-secondary text-uppercase text-center">Estatus</th>
                                <th class="py-3 text-secondary text-uppercase">Notas</th>
                                <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                                    <th class="text-center py-3 text-secondary text-uppercase">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vacaciones as $v): 
                                $clase_badge = 'bg-secondary';
                                if($v['estatus'] === 'aprobado')  $clase_badge = 'text-success bg-success-subtle border border-success-subtle';
                                if($v['estatus'] === 'pendiente') $clase_badge = 'text-warning bg-warning-subtle border border-warning-subtle';
                            ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">👤 <?php echo htmlspecialchars($v['empleado']); ?></td>
                                    <td class="text-center text-secondary fw-medium">
                                        📅 <?php echo date('d/m/Y', strtotime($v['fecha_inicio'])); ?> ➡️ <?php echo date('d/m/Y', strtotime($v['fecha_fin'])); ?>
                                    </td>
                                    <td class="text-center fw-bold text-primary fs-6"><?php echo (int)$v['dias_solicitados']; ?> días</td>
                                    <td class="text-center">
                                        <span class="badge text-capitalize rounded-pill px-3 py-1 <?php echo $clase_badge; ?>"><?php echo $v['estatus']; ?></span>
                                    </td>
                                    <td class="text-muted text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($v['observaciones'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($v['observaciones'] ?? 'Sin notas'); ?>
                                    </td>
                                    <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                                        <td class="text-center">
                                            <a href="index.php?accion=eliminar_vacacion&id=<?php echo (int)$v['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('¿Eliminar registro?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> <!-- Fin row operativo -->
</div> <!-- Fin container-fluid -->

<!-- MOTOR DE CONTROL JAVASCRIPT: DATATABLES EN ESPAÑOL Y EVALUACIÓN DE SALDOS -->
<script>
$(document).ready(function() {
    // 1. Inicialización de DataTables para el Historial de Vacaciones
    if ($.fn.DataTable) {
        $('#tablaVacaciones').DataTable({
            "pageLength": 10,
            "lengthMenu": [[5, 10, 20, 50, -1], [5, 10, 20, 50, "Todos"]],
            "order": [[1, "desc"]], // Ordenación inicial por periodo (segunda columna) descendente
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se registran solicitudes de vacaciones.",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "No hay solicitudes cargadas",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar rápido:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    }

    // 2. Validador y Candado en tiempo real para evitar excedentes de días
    const form       = document.getElementById('formVacaciones');
    const selUser    = document.getElementById('selectUsuarioVac');
    const fIni       = document.getElementById('fecha_ini');
    const fFin       = document.getElementById('fecha_fin');
    const btnGuardar = document.getElementById('btnGuardarVac');
    const alertEx    = document.getElementById('alertaExcesoDias');

    function evaluarDiasDisponibles() {
        if (!selUser || !fIni.value || !fFin.value) return;

        // Recuperamos el saldo disponible guardado en los atributos 'data-' de la opción activa
        const optionSelected = selUser.options[selUser.selectedIndex];
        const disponibles = parseInt(optionSelected.getAttribute('data-disponibles')) || 0;

        // Calculamos la diferencia matemática neta en días calendario
        const inicio = new Date(fIni.value + 'T00:00:00');
        const fin    = new Date(fFin.value + 'T00:00:00');
        
        const diferenciaTiempo = fin.getTime() - inicio.getTime();
        const diasSolicitados  = Math.floor(diferenciaTiempo / (1000 * 3600 * 24)) + 1;

        // Activamos cortocircuito visual y bloqueamos el botón verde si hay inconsistencias o sobregiros
        if (diasSolicitados > disponibles || diasSolicitados <= 0) {
            alertEx.classList.remove('d-none');
            if (btnGuardar) btnGuardar.disabled = true; // BLOQUEO STRICT DEL BOTÓN
        } else {
            alertEx.classList.add('d-none');
            if (btnGuardar) btnGuardar.disabled = false; // SE LIBERA SI EL SALDO ES LEGAL
        }
    }

    if (form) {
        fIni.addEventListener('change', evaluarDiasDisponibles);
        fFin.addEventListener('change', evaluarDiasDisponibles);
        selUser.addEventListener('change', evaluarDiasDisponibles);
    }
});
</script>

<?php 
// 3. Cargamos de forma modular el pie de página común que cierra el documento HTML
require_once 'views/partials/footer.php'; 
?>
