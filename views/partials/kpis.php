<!-- SECCIÓN DE INDICADORES NUMÉRICOS DINÁMICOS POR MES Y AÑO (KPIs) -->
<div class="row g-3 mb-4">
    
    <!-- Tarjeta 1: Por Asignar -->
    <div class="col col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-4 border-secondary bg-secondary-subtle bg-opacity-25 rounded-3">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Por Asignar</div>
                    <h3 class="fw-bold text-dark m-0 fs-2" id="kpi-por-asignar"><?php echo (int)($kpi['por_asignar'] ?? 0); ?></h3>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        📅 Período: <strong class="text-dark-emphasis"><?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?></strong>
                    </div>
                </div>
                <!--<div class="fs-1 text-secondary opacity-50">📂</div>-->
                <i class="bi  bi-question-circle fs-1 text-danger opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Tarjeta 2: Pendientes -->
    <div class="col col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning bg-warning-subtle bg-opacity-25 rounded-3">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-warning-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pendientes</div>
                    <h3 class="fw-bold text-dark m-0 fs-2" id="kpi-pendiente"><?php echo (int)($kpi['pendiente'] ?? 0); ?></h3>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        📅 Período: <strong class="text-dark-emphasis"><?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?></strong>
                    </div>
                </div>
                <!--<div class="fs-1 text-warning opacity-50">⏳</div>-->
                 <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Tarjeta 3: En Proceso -->
    <div class="col col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary bg-primary-subtle bg-opacity-25 rounded-3">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-primary-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">En Proceso</div>
                    <h3 class="fw-bold text-dark m-0 fs-2" id="kpi-proceso"><?php echo (int)($kpi['proceso'] ?? 0); ?></h3>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        📅 Período: <strong class="text-dark-emphasis"><?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?></strong>
                    </div>
                </div>
                <!--<div class="fs-1 text-primary opacity-50">⚙️</div>-->
                <i class="bi bi-gear-fill fs-1 text-secondary opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Tarjeta 4: Finalizados -->
    <div class="col col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0 border-start border-4 border-success bg-success-subtle bg-opacity-25 rounded-3">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-success-emphasis small fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Finalizados</div>
                    <h3 class="fw-bold text-dark m-0 fs-2" id="kpi-finalizado"><?php echo (int)($kpi['finalizado'] ?? 0); ?></h3>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        📅 Período: <strong class="text-dark-emphasis"><?php echo htmlspecialchars($kpi['periodo_nombre'] ?? ''); ?></strong>
                    </div>
                </div>
                <i class="bi bi-check-circle-fill fs-1 text-success opacity-50"></i>
            </div>
        </div>
    </div>

</div>
