<?php
// Capturamos la acción y el rol actual para la lógica de visualización adaptativa por privilegios
$accion_actual = isset($_GET['accion']) ? $_GET['accion'] : 'dashboard';
$rol_usuario   = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid px-4">
        <!-- Logotipo / Título Principal de la Plataforma -->
        <a class="navbar-brand fw-bold text-primary" href="index.php?accion=dashboard">🎫 Panel Central</a>
        
        <!-- BOTÓN DE HAMBURGUESA: Configurado de manera simétrica para desplegarse fluidamente en móviles -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- CONTENEDOR COLAPSABLE RESPONSIVO -->
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <!-- SECCIÓN IZQUIERDA: Enlaces de navegación principal controlados estrictamente por Rol -->
            <div class="navbar-nav me-auto">
                <!-- 1. ACTIVIDADES: Visible únicamente para BÁSICO y ADMINISTRADOR (El Intermedio NO lo ve) -->
                <?php if ($rol_usuario === 'basico' || $rol_usuario === 'administrador'): ?>
                    <a class="nav-link <?php echo ($accion_actual == 'dashboard') ? 'active fw-semibold' : ''; ?>" href="index.php?accion=dashboard">Actividades y Requerimientos</a>
                <?php endif; ?>
                
                <!-- 2. TICKETS: Visible únicamente para INTERMEDIO y ADMINISTRADOR (El Básico NO lo ve) -->
                <?php if ($rol_usuario === 'intermedio' || $rol_usuario === 'administrador'): ?>
                    <a class="nav-link <?php echo ($accion_actual == 'tickets') ? 'active fw-semibold' : ''; ?>" href="index.php?accion=tickets">Tickets</a>
                <?php endif; ?>

                <!-- 3. VACACIONES GLOBALES: Accesible para todos los perfiles de la organización (LFT) -->
                <a class="nav-link <?php echo ($accion_actual == 'vacaciones') ? 'active fw-semibold' : ''; ?>" href="index.php?accion=vacaciones">Vacaciones</a>

                <!-- 4. INVENTARIO GLOBAL: Accesible para todos los perfiles (Básico descuenta, Intermedio/Admin editan) -->
                <a class="nav-link <?php echo ($accion_actual == 'inventario') ? 'active fw-semibold' : ''; ?>" href="index.php?accion=inventario">Inventario TI</a>

                <!-- 5. USUARIOS (CRUD): Filtro exclusivo asignado única y directamente al ADMINISTRADOR corporativo -->
                <?php if ($rol_usuario === 'administrador'): ?>
                    <a class="nav-link <?php echo ($accion_actual == 'usuarios') ? 'active fw-semibold' : ''; ?>" href="index.php?accion=usuarios">Usuarios</a>
                <?php endif; ?>
            </div>
            
            <!-- SECCIÓN DERECHA: Dropdown adaptivo con Miniatura Circular de la Foto de Perfil actual -->
            <div class="navbar-nav ms-auto align-items-center">
                <div class="nav-item dropdown w-100 text-end">
                    
                    <!-- Disparador interactivo del menú desplegable derecho -->
                    <a class="nav-link dropdown-toggle text-light small py-1.5 px-3 bg-secondary bg-opacity-25 rounded-3 d-inline-flex align-items-center align-middle" href="#" id="navbarDropdownMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- CONTENEDOR CIRCULAR DE LA FOTO EN MINIATURA -->
                        <div class="rounded-circle border overflow-hidden d-inline-block me-2" style="width: 24px; height: 24px; background-color: #fff;">
                            <?php 
                            // Si el usuario subió una foto, lee su ruta desde la sesión; de lo contrario toma el avatar por defecto
                            $menu_avatar = (isset($_SESSION['usuario_foto']) && !empty($_SESSION['usuario_foto'])) ? $_SESSION['usuario_foto'] : 'assets/img/default-avatar.png'; 
                            ?>
                            <!-- El timestamp (?t=time) fuerza al navegador a romper la caché de la imagen e ilustrar los cambios de foto en caliente -->
                            <img src="<?php echo $menu_avatar; ?>?t=<?php echo time(); ?>" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        <span>
                            <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Invitado'); ?></strong> 
                            (<span class="text-info text-capitalize"><?php echo htmlspecialchars($rol_usuario); ?></span>)
                        </span>
                    </a>
                    
                    <!-- Lista flotante de opciones del Perfil Privado -->
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" aria-labelledby="navbarDropdownMenu">
                        <li>
                            <!-- Opción 1: Redirige físicamente al archivo de Perfil Completo para cambiar Nombre/Foto -->
                            <a class="dropdown-item py-2 <?php echo ($accion_actual == 'mi_perfil') ? 'active fw-bold' : ''; ?>" href="index.php?accion=mi_perfil">
                                ⚙️ Ver Mi Perfil
                            </a>
                        </li>
                        <li>
                            <!-- Opción 2: Llama de manera síncrona a la ventana modal flotante central mediante atributos data-bs de Bootstrap -->
                            <button type="button" class="dropdown-item py-2 text-warning-emphasis" data-bs-toggle="modal" data-bs-target="#modalCambiarMiPasswordGlobal">
                                🔑 Cambiar Contraseña
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger fw-bold" href="index.php?accion=logout">
                                🚪 Cerrar Sesión
                            </a>
                        </li>
                    </ul>

                </div>
            </div>

        </div>
    </div>
</nav>

<!-- ========================================================================== -->
<!-- VENTANA MODAL GLOBAL: CAMBIO DE CONTRASEÑA PROPIA CON DETECTOR DE HARDWARE -->
<!-- ========================================================================== -->
<div class="modal fade" id="modalCambiarMiPasswordGlobal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 22rem;">
        <div class="modal-content border-0 shadow-lg text-start">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6">🔒 Cambiar mi Contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Apunta de forma directa a la acción que procesa la encriptación hash (BCRYPT) en el backend -->
            <form action="index.php?accion=actualizar_mi_password" method="POST">
                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-muted mb-3 bg-light bg-opacity-50" style="font-size: 0.72rem;">
                        🛡️ Al confirmar, tu clave de acceso se renovará de forma encriptada inmediatamente.
                    </div>
                    
                    <!-- Campo 1: Nueva Contraseña -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nueva Contraseña</label>
                        <div class="input-group input-group-sm">
                            <input type="password" name="nueva_password" id="pass-nueva" class="form-control border-secondary-subtle" minlength="6" placeholder="Mínimo 6 caracteres" required>
                            <button class="btn btn-outline-secondary border-secondary-subtle toggler-pass" type="button" data-target="pass-nueva">👁️</button>
                        </div>
                        <!-- Alerta Dinámica de Bloq Mayús -->
                        <div id="caps-alert-nueva" class="text-warning small fw-bold mt-1 d-none" style="font-size: 0.72rem;">⚠️ Bloq Mayús activado</div>
                    </div>
                    <!-- Campo 2: Confirmar Contraseña -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Confirmar Nueva Contraseña</label>
                        <div class="input-group input-group-sm">
                            <input type="password" name="confirm_password" id="pass-confirm" class="form-control border-secondary-subtle" minlength="6" placeholder="Repite la clave exactamente igual..." required>
                            <button class="btn btn-outline-secondary border-secondary-subtle toggler-pass" type="button" data-target="pass-confirm">👁️</button>
                        </div>
                        <!-- Alerta Dinámica de Bloq Mayús -->
                        <div id="caps-alert-confirm" class="text-warning small fw-bold mt-1 d-none" style="font-size: 0.72rem;">⚠️ Bloq Mayús activado</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Regresar</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm text-dark">Aplicar Cambio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LÓGICA JAVASCRIPT GLOBAL SÍNCRONA INTERACTIVA DEL NAVBAR -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Mostrar / Ocultar Contraseña de forma dinámica (Intercambiador Ojo / Mono ciego)
    document.querySelectorAll('.toggler-pass').forEach(button => {
        button.addEventListener('click', function() {
            const inputId = this.getAttribute('data-target');
            const inputField = document.getElementById(inputId);
            if (inputField.type === 'password') {
                inputField.type = 'text';
                this.innerText = '🙈';
            } else {
                inputField.type = 'password';
                this.innerText = '👁️';
            }
        });
    });

    // 2. Evaluador en tiempo real del estado de hardware de las teclas del cliente (Caps Lock activado)
    const inputsModal = [
        { field: document.getElementById('pass-nueva'), alert: document.getElementById('caps-alert-nueva') },
        { field: document.getElementById('pass-confirm'), alert: document.getElementById('caps-alert-confirm') }
    ];

    inputsModal.forEach(item => {
        if (item.field) {
            item.field.addEventListener('keyup', (e) => {
                // Interroga el getModifierState físico del teclado
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    item.alert.classList.remove('d-none');
                } else {
                    item.alert.classList.add('d-none');
                }
            });
            // Remueve de forma inmediata la alerta cuando el operador abandona el input (onblur)
            item.field.addEventListener('blur', () => item.alert.classList.add('d-none'));
        }
    });
});
</script>
