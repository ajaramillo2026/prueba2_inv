<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small w-100">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="ps-3 py-3 text-secondary text-uppercase" style="width: 40%;">Descripción del Insumo</th>
                        <th class="py-3 text-secondary text-uppercase" style="width: 20%;">Categoría</th>
                        <th class="py-3 text-secondary text-uppercase text-center" style="width: 20%;">Existencias Disponibles</th>
                        <th class="text-center py-3 text-secondary text-uppercase" style="width: 20%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark fs-6 text-uppercase">
                                    📦 <?php echo htmlspecialchars($p['nombre']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill"><?php echo $p['categoria']; ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($p['stock'] < 5): ?>
                                        <span class="badge bg-danger text-white px-3 py-1.5 rounded-3 fw-bold d-inline-block shadow-sm">
                                            🚨 Crítico: <?php echo (int)$p['stock']; ?> uds.
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1.5 rounded-3 fw-bold d-inline-block">
                                            📦 <?php echo (int)$p['stock']; ?> uds.
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-2">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger shadow-sm px-2.5 btn-descontar-accion" 
                                                data-id="<?php echo $p['id']; ?>" 
                                                data-nombre="<?php echo mb_strtoupper(htmlspecialchars($p['nombre']), 'UTF-8'); ?>"
                                                data-max="<?php echo (int)$p['stock']; ?>"
                                                title="Descontar piezas del stock">
                                            Descontar
                                        </button>
                                        
                                        <?php if ($_SESSION['usuario_tipo'] !== 'basico'): ?>
                                            <a href="index.php?accion=inventario&editar_id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-warning text-dark" title="Editar Ficha"><i class="bi bi-pencil-square"></i></a>
                                            <?php if ($_SESSION['usuario_tipo'] === 'administrador'): ?>
                                                <a href="index.php?accion=eliminar_inventario&id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-danger shadow-sm px-2" onclick="return confirm('¿Seguro que deseas eliminar permanentemente este producto?')" title="Eliminar"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted fs-6">Ningún producto registrado bajo estos filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
      <!-- BARRA INFERIOR ACTUALIZADA CON BOTONERA NUMÉRICA INTERACTIVA -->
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3 rounded-bottom-3">
        <div class="text-muted small">
            Mostrando registros de la página <strong><?php echo $pagina_actual; ?></strong> de <strong><?php echo $total_paginas_inventario; ?></strong> (Total: <?php echo $total_registros_inventario; ?> insumos)
        </div>
        <nav aria-label="Navegación de inventario">
            <ul class="pagination pagination-sm m-0 shadow-sm">
                
                <!-- 1. Botón: Anterior -->
                <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="index.php?accion=inventario&p=<?php echo $pagina_actual - 1; ?>&f_nombre=<?php echo urlencode($_GET['f_nombre'] ?? ''); ?>&f_bajo_stock=<?php echo urlencode($_GET['f_bajo_stock'] ?? ''); ?>&f_limite=<?php echo $registros_por_pagina; ?>" aria-label="Anterior">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>

                <!-- 2. BUCLE FOR: Renderiza dinámicamente cada número de página -->
                <?php 
                for ($i = 1; $i <= $total_paginas_inventario; $i++): 
                    // Si el número de iteración coincide con la página que ve el usuario, enciende el color azul con 'active'
                    $clase_activa = ($i === $pagina_actual) ? 'active fw-bold' : '';
                ?>
                    <li class="page-item <?php echo $clase_activa; ?>">
                        <a class="page-link" href="index.php?accion=inventario&p=<?php echo $i; ?>&f_nombre=<?php echo urlencode($_GET['f_nombre'] ?? ''); ?>&f_bajo_stock=<?php echo urlencode($_GET['f_bajo_stock'] ?? ''); ?>&f_limite=<?php echo $registros_por_pagina; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- 3. Botón: Siguiente -->
                <li class="page-item <?php echo ($pagina_actual >= $total_paginas_inventario) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="index.php?accion=inventario&p=<?php echo $pagina_actual + 1; ?>&f_nombre=<?php echo urlencode($_GET['f_nombre'] ?? ''); ?>&f_bajo_stock=<?php echo urlencode($_GET['f_bajo_stock'] ?? ''); ?>&f_limite=<?php echo $registros_por_pagina; ?>" aria-label="Siguiente">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</div> <!-- Fin de la Card General -->



