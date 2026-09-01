<?php 
// 1. Cargamos de forma modular la cabecera HTML y la barra de navegación comunes
require_once 'views/partials/header.php'; 
require_once 'views/partials/navbar.php'; 

// Cargamos la foto de perfil del usuario o el avatar genérico si no se ha subido ninguna
$foto_actual = isset($usuario['foto_perfil']) && !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'assets/img/default-avatar.png';
?>

<div class="container py-4">
    <!-- SECCIÓN DE ALERTAS DINÁMICAS DE RETROALIMENTACIÓN -->
    <div class="row justify-content-center mb-2">
        <div class="col-md-8 col-lg-6">
            <?php if (isset($_SESSION['exito_perfil'])): ?>
                <div class="alert alert-success alert-dismissible fade show small text-center shadow-sm border-0 mb-3" role="alert">
                    <?php echo $_SESSION['exito_perfil']; unset($_SESSION['exito_perfil']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_perfil'])): ?>
                <div class="alert alert-danger alert-dismissible fade show small text-center shadow-sm border-0 mb-3" role="alert">
                    <?php echo $_SESSION['error_perfil']; unset($_SESSION['error_perfil']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CUERPO PRINCIPAL DEL PERFIL DE CUENTA -->
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="card shadow border-0 rounded-3 overflow-hidden">
                <div class="card-header bg-dark text-white py-3 text-center">
                    <h5 class="m-0 fw-bold fs-6">👤 Mi Perfil de Cuenta</h5>
                </div>
                
                <div class="card-body p-4 bg-white">
                    <!-- PREVISUALIZACIÓN DE AVATAR EN TIEMPO REAL -->
                    <div class="text-center mb-4">
                        <div class="d-inline-block rounded-circle border overflow-hidden shadow-sm mb-2" style="width: 110px; height: 100px;">
                            <!-- Forzamos al navegador a romper la caché de la imagen con la marca de tiempo (?t=) -->
                            <img src="<?php echo $foto_actual; ?>?t=<?php echo time(); ?>" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        <h4 class="fw-bold text-dark m-0 fs-5"><?php echo htmlspecialchars($usuario['nombre']); ?></h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase px-2.5 py-1 rounded-pill mt-2" style="font-size: 0.7rem;">
                            Rol: <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                        </span>
                    </div>

                    <!-- DATOS FISCALES / METADATOS DE CUENTA -->
                    <div class="row g-2 border-top pt-3 text-secondary small mb-4">
                        <div class="col-6 fw-bold">ID único de Registro:</div>
                        <div class="col-6 text-dark text-end">#<?php echo (int)$_SESSION['usuario_id']; ?></div>
                        <div class="col-6 fw-bold">Usuario de Acceso:</div>
                        <div class="col-6 text-dark text-end">@<?php echo htmlspecialchars($_SESSION['usuario_login']); ?></div>
                    </div>

                    <!-- FORMULARIO DE ACTUALIZACIÓN DE DATOS PERSONALES -->
                    <form action="index.php?accion=actualizar_mis_datos" method="POST" enctype="multipart/form-data" class="border-top pt-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Nombre Real Completo</label>
                            <input type="text" name="nombre" class="form-control form-control-sm border-secondary-subtle py-2 text-dark fw-medium" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required placeholder="Escribe tu nombre completo...">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Cambiar Imagen de Perfil (JPG, JPEG, PNG)</label>
                            <input type="file" name="foto_perfil" class="form-control form-control-sm border-secondary-subtle" accept=".jpg, .jpeg, .png">
                            <div class="form-text text-muted" style="font-size: 0.68rem;">Nota: Para un mejor acabado visual, utiliza una foto cuadrada.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold-2 shadow-sm"><i class="bi bi-floppy2"></i> </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
// 3. Cargamos de forma modular el pie de página común que cierra el documento y los scripts
require_once 'views/partials/footer.php'; 
?>
