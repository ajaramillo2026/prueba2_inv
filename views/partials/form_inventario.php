<?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white fw-bold py-3">
            <?php echo isset($itemEditar) ? '✏️ Modificar Ficha de Producto' : '📦 Registrar Insumo TI'; ?>
        </div>
        <div class="card-body p-4">
            <form action="index.php?accion=guardar_inventario" method="POST">
                <input type="hidden" name="id" value="<?php echo $itemEditar['id'] ?? ''; ?>">

                <!-- Selector Maestro de Lista de Existentes -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Seleccionar Producto de la Lista</label>
                    <select id="selectorListaProductos" name="producto_existente_id" class="form-select border-primary border-opacity-50 fw-semibold" onchange="alternarModoFormulario()">
                        <option value="NUEVO" <?php echo !isset($itemEditar) ? 'selected' : ''; ?>>➕ [ Registrar como Producto Nuevo ]</option>
                        <?php 
                        $modeloParaLista = new InventarioModel();
                        $todosLosInsumos = $modeloParaLista->obtenerFiltrados([]);
                        foreach ($todosLosInsumos as $insumo): 
                        ?>
                            <!-- Se formatea el nombre visual de la lista en mayúsculas -->
                            <option value="<?php echo $insumo['id']; ?>" 
                                    data-nombre="<?php echo mb_strtoupper(htmlspecialchars($insumo['nombre']), 'UTF-8'); ?>"
                                    data-categoria="<?php echo $insumo['categoria']; ?>"
                                    <?php echo (isset($itemEditar) && $itemEditar['id'] == $insumo['id']) ? 'selected' : ''; ?>>
                                📦 <?php echo mb_strtoupper(htmlspecialchars($insumo['nombre']), 'UTF-8'); ?> (Actual: <?php echo $insumo['stock']; ?> u)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Campo: Nombre (CONVERSOR AUTOMÁTICO A MAYÚSCULAS EN TIEMPO REAL) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Nombre del Producto / Modelo</label>
                    <input type="text" name="nombre" id="inputNombreProd" class="form-control border-secondary-subtle fw-bold text-dark" 
                           value="<?php echo isset($itemEditar) ? mb_strtoupper($itemEditar['nombre'], 'UTF-8') : ''; ?>" 
                           placeholder="Ej: MONITOR DELL 24 PULGADAS" required
                           oninput="this.value = this.value.toUpperCase()">
                </div>

                <div class="row g-2 mb-4">
                    <!-- Campo: Categoría -->
                    <div class="col-6">
                        <label class="form-label small fw-bold text-secondary">Categoría</label>
                        <select name="categoria" id="inputCatProd" class="form-select border-secondary-subtle text-dark" required>
                            <option value="Computo" <?php echo (isset($itemEditar) && $itemEditar['categoria'] === 'Computo') ? 'selected' : ''; ?>>Computo</option>
                            <option value="Redes" <?php echo (isset($itemEditar) && $itemEditar['categoria'] === 'Redes') ? 'selected' : ''; ?>>Redes</option>
                            <option value="Perifericos" <?php echo (isset($itemEditar) && $itemEditar['categoria'] === 'Perifericos') ? 'selected' : ''; ?>>Perifericos</option>
                            <option value="Consumibles" <?php echo (isset($itemEditar) && $itemEditar['categoria'] === 'Consumibles') ? 'selected' : ''; ?>>Consumibles</option>
                            <option value="Otros" <?php echo (isset($itemEditar) && $itemEditar['categoria'] === 'Otros') ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    <!-- Cantidad de Stock -->
                    <div class="col-6">
                        <label class="form-label small fw-bold text-dark" id="labelStockDinamico">Cantidad Inicial</label>
                        <input type="number" name="stock" class="form-control border-secondary-subtle text-center fw-bold text-dark" min="0" 
                               value="<?php echo isset($itemEditar) ? (int)$itemEditar['stock'] : '1'; ?>" required>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm"><i class="bi bi-floppy2"></i> Procesar Almacén</button>
                    <?php if (isset($itemEditar)): ?>
                        <a href="index.php?accion=inventario" class="btn btn-light btn-sm border py-2">Cancelar Operación</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
