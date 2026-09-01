<?php 
require_once 'views/partials/header.php'; 
require_once 'views/partials/navbar.php'; 
?>

<div class="container-fluid px-4">
    <h2 class="mb-4 text-secondary fw-bold fs-3">Control de Inventario Tecnológico</h2>

    <!-- ALERTAS OPERATIVAS DEL SISTEMA -->
    <?php if (isset($_SESSION['exito_inventario'])): ?>
        <div class="alert alert-success border-0 shadow-sm text-center small mb-3 alert-dismissible fade show">
            <?php echo $_SESSION['exito_inventario']; unset($_SESSION['exito_inventario']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_inventario'])): ?>
        <div class="alert alert-danger border-0 shadow-sm text-center small mb-3 alert-dismissible fade show">
            <?php echo $_SESSION['error_inventario']; unset($_SESSION['error_inventario']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN 1: FILTROS DINÁMICOS E IMPORTADOR CSV -->
    <div class="row g-3 mb-4">
        <div class="col-xl-7 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3 h-100 card-body bg-white py-3 justify-content-center">
                <form method="GET" action="index.php" class="row g-2 align-items-end">
                    <input type="hidden" name="accion" value="inventario">
                    <input type="hidden" name="p" value="1"> <!-- Resetea a la página 1 al buscar -->
                    
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Buscar por Nombre</label>
                        <input type="text" name="f_nombre" class="form-control form-control-sm border-secondary-subtle py-1.5" 
                            value="<?php echo htmlspecialchars($_GET['f_nombre'] ?? ''); ?>" placeholder="Ej: Monitor Dell...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Filtro de Stock</label>
                        <select name="f_bajo_stock" class="form-select form-select-sm border-secondary-subtle">
                            <option value="">Todos los Productos</option>
                            <option value="si" <?php echo (isset($_GET['f_bajo_stock']) && $_GET['f_bajo_stock'] === 'si') ? 'selected' : ''; ?>>🚨 Existencias Críticas (< 5 uds)</option>
                        </select>
                    </div>

                    <!-- NUEVO: Selector de cantidad de registros a mostrar (Igual a Actividades) -->
                     <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary">Mostrar filas</label>
                        <select name="f_limite" class="form-select form-select-sm border-secondary-subtle">
                            <option value="10" <?php echo ($registros_por_pagina == 10) ? 'selected' : ''; ?>>10 filas</option>
                            <option value="20" <?php echo ($registros_por_pagina == 20) ? 'selected' : ''; ?>>20 filas</option>
                            <option value="30" <?php echo ($registros_por_pagina == 30) ? 'selected' : ''; ?>>30 filas</option>
                        </select>
                    </div>

                    <div class="col d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold py-1.5 shadow-sm">Buscar</button>
                        <a href="index.php?accion=exportar_inventario&f_nombre=<?php echo urlencode($_GET['f_nombre'] ?? ''); ?>&f_bajo_stock=<?php echo urlencode($_GET['f_bajo_stock'] ?? ''); ?>" 
                        class="btn btn-outline-success btn-sm fw-bold shadow-sm px-2 py-1.5" title="Excel">📊</a>
                    </div>
                </form>

            </div>
        </div>

        <div class="col-xl-5 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3 h-100 card-body bg-white py-3 justify-content-center">
                <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                    <form action="index.php?accion=importar_inventario_csv" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">📥 Carga Masiva de Productos (.CSV)</label>
                            <input type="file" name="archivo_csv" class="form-control form-control-sm border-secondary-subtle" accept=".csv" required>
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="submit" class="btn btn-dark btn-sm fw-bold py-2 shadow-sm" onclick="return confirm('¿Confirmas la carga masiva?')">⚡ Importar</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border small text-muted m-0 py-2.5">ℹ️ Tu nivel de rol operativo no cuenta con permisos para ejecutar cargas masivas CSV.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: CUERPO GENERAL EN REJILLA DOS COLUMNAS -->
    <div class="row g-4">
        <div class="col-xl-4 col-lg-5">
            <?php require_once 'views/partials/form_inventario.php'; ?>
        </div>
        <div class="<?php echo ($_SESSION['usuario_tipo'] !== 'basico') ? 'col-xl-8 col-lg-7' : 'col-12'; ?>">
            <?php require_once 'views/partials/tabla_inventario.php'; ?>
        </div>
    </div>
</div>

<!-- ========================================================================== -->
<!-- VENTANA MODAL GLOBAL: DESCONTAR PIEZAS DEL ALMACÉN (BLINDADA CONTRA CHOQUES) -->
<!-- ========================================================================== -->
<div class="modal fade" id="modalDescontarStock" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 22rem;">
        <div class="modal-content border-0 shadow-lg text-start">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6">📦 Registrar Salida de Inventario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Apunta de manera directa a la acción calibrada en tu index.php -->
            <form action="index.php?accion=descontar_stock_inventario" method="POST">
                <div class="modal-body p-4">
                    <!-- CORREGIDO: Se asignó un ID único e irrepetible para evitar colisiones con otros formularios -->
                    <input type="hidden" name="id_descontar" id="descontar-id-almacen">
                    
                    <div class="mb-3 small text-muted">
                        Insumo a modificar: <strong id="desc-nombre" class="text-dark"></strong>
                    </div>
                    <div class="mb-3 small text-secondary">
                        Existencias disponibles actuales: <strong id="desc-max-visual" class="text-primary"></strong> pzas.
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary">Cantidad de piezas a retirar</label>
                        <!-- Name exacto alineado con el POST del controlador -->
                        <input type="number" name="cantidad_descontar" id="inputCantidadDescontar" class="form-control form-control-sm border-secondary-subtle py-2 text-center fw-bold fs-5 text-dark" min="1" value="1" required>
                        <div class="form-text text-muted" style="font-size:0.68rem;">Nota: Esta operación restará de forma directa el stock general de la base de datos.</div>
                    </div>

                    <!-- Alerta de Bloqueo por sobregiro de existencias -->
                    <div id="alertaExcesoStock" class="alert alert-warning border-0 small py-2 m-0 mt-2 d-none">
                        ⚠️ Error: No puedes retirar más unidades de las disponibles en almacén.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Regresar</button>
                    <button type="submit" id="btnConfirmarDescuento" class="btn btn-danger btn-sm fw-bold shadow-sm">Confirmar Descuento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MOTOR DE RESPUESTA INTERACTIVA GLOBAL (COMBINADO Y BLINDADO) -->
<!-- MOTOR DE RESPUESTA INTERACTIVA GLOBAL (DATA TABLES COMPACTO 5 REGISTROS + CANDADOS DE ALMACÉN) -->
<script>
// ==========================================================================
// 1. CONTROL DEL FORMULARIO HÍBRIDO (ALTAS NUEVAS / ACUMULAR EXISTENCIAS)
// ==========================================================================
function alternarModoFormulario() {
    const selector = document.getElementById('selectorListaProductos');
    const inputNombre = document.getElementById('inputNombreProd');
    const inputCat = document.getElementById('inputCatProd');
    const labelStock = document.getElementById('labelStockDinamico');
    if (!selector || !inputNombre) return;

    if (selector.value === 'NUEVO') {
        // MODO ALTA NUEVA: Limpiamos y liberamos los campos para escritura manual
        inputNombre.value = ''; 
        inputCat.value = 'Computo';
        inputNombre.readOnly = false; 
        inputCat.disabled = false;
        if (labelStock) labelStock.innerText = "⚡ Cantidad Inicial";
    } else {
        // MODO ACUMULAR STOCK: Rescatamos el nodo option seleccionado
        const op = selector.options[selector.selectedIndex];
        const nombreProducto = op.getAttribute('data-nombre');
        const categoriaProducto = op.getAttribute('data-categoria');
        
        // Autocompletamos y bloqueamos los campos fijos en mayúsculas
        inputNombre.value = nombreProducto;
        inputCat.value = categoriaProducto;
        inputNombre.readOnly = true; 
        inputCat.disabled = true;
        if (labelStock) labelStock.innerText = "➕ Cantidad a Sumar";
    }
}

// ==========================================================================
// 2. INICIALIZACIÓN DE COMPONENTES CUANDO EL DOM ESTÁ COMPLETAMENTE LISTO
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    
    // Forzamos la ejecución inicial de la lista híbrida
    alternarModoFormulario();
    
    // HABILITACIÓN DE DATATABLES CON AJUSTE ELÁSTICO DE 5 REGISTROS
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#tablaInventario').DataTable({
            "pageLength": 5, // AJUSTADO: Carga inicial por defecto de 5 registros
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]], // Mantiene la opción de escalar el listado
            "order": [[2, "asc"]], // Muestra de forma prioritaria los insumos más escasas
            
            // Distribución estructural responsiva de los elementos en Bootstrap 5
            "dom": "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-end'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 text-end'p>>",
                   
            "language": {
                "lengthMenu": "Mostrar _MENU_ insumos",
                "zeroRecords": "Ningún producto coincide con los filtros establecidos.",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                "infoEmpty": "No hay productos disponibles en el almacén",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar rápido en tabla:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });
    }

    // Elementos del DOM del formulario modal (Capturados de forma nativa)
    const inputCant    = document.getElementById('inputCantidadDescontar');
    const btnConfirmar = document.getElementById('btnConfirmarDescuento');
    const alertaStock  = document.getElementById('alertaExcesoStock');
    let maxDisponible   = 0;

    // ==========================================================================
    // 3. PASARELA DE ENTRADA Y EGRESO: INTERCEPTOR DE CLIC GLOBAL (VANILLA JS)
    // ==========================================================================
    document.body.addEventListener('click', function(event) {
        
        // Buscamos si el elemento clickeado (o su padre directo) contiene la clase de control
        const boton = event.target.closest('.btn-descontar-accion');
        
        if (boton) {
            event.preventDefault(); // Detiene comportamientos url por defecto

            // Extraemos los metadatos puros del nodo de la fila de DataTables
            const idProducto  = boton.getAttribute('data-id');
            const nomProducto = boton.getAttribute('data-nombre');
            maxDisponible     = parseInt(boton.getAttribute('data-max')) || 0;

            // INYECCIÓN MÁXIMA FORZADA EN LOS CAMPOS CON NUEVOS IDS ÚNICOS DE LA VENTANA MODAL
            document.getElementById('descontar-id-almacen').value = idProducto;
            document.getElementById('desc-nombre').innerText = nomProducto;
            document.getElementById('desc-max-visual').innerText = maxDisponible;

            // Reseteamos las condiciones iniciales del input y alertas
            if (inputCant) {
                inputCant.value = 1;
                inputCant.setAttribute('max', maxDisponible);
            }
            if (alertaStock)  alertaStock.classList.add('d-none');
            if (btnConfirmar) btnConfirmar.disabled = false;

            // DISPARO Y RENDERIZACIÓN DE LA MODAL POR API NATIVA DE BOOTSTRAP 5
            const modalElement = document.getElementById('modalDescontarStock');
            if (modalElement) {
                const miModalNativa = bootstrap.Modal.getOrCreateInstance(modalElement);
                miModalNativa.show();
            }
        }
    });

    // ==========================================================================
    // 4. CANDADO PREVENTIVO EN TIEMPO REAL: EVALÚA EL LÍMITE FÍSICO DE ALMACÉN
    // ==========================================================================
    if (inputCant) {
        inputCant.addEventListener('input', function() {
            const solicitado = parseInt(this.value) || 0;

            if (solicitado > maxDisponible || solicitado <= 0) {
                if (alertaStock)  alertaStock.classList.remove('d-none');
                if (btnConfirmar) btnConfirmar.disabled = true; // CONGELA EL BOTÓN DE ENVÍO
            } else {
                if (alertaStock)  alertaStock.classList.add('d-none');
                if (btnConfirmar) btnConfirmar.disabled = false; // LIBERA EL BOTÓN
            }
        });
    }
});
</script>

<?php 
// 3. Cargamos de forma modular el pie de página común que cierra la vista de forma elástica
require_once 'views/partials/footer.php'; 
?>
