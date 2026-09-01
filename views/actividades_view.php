<?php 
// 1. Cargamos de forma modular la cabecera HTML y la barra de navegación comunes con Bootstrap Local
require_once 'views/partials/header.php'; 
require_once 'views/partials/navbar.php'; 
?>

<div class="container-fluid px-4">

    <!-- 2. Cargamos la sección superior de tarjetas numéricas KPI -->
    <?php require_once 'views/partials/kpis.php'; ?>

    <!-- CUERPO PRINCIPAL DEL PANEL: Distribución Dinámica por Privilegios de Roles -->
    <div class="row g-4">
        
        <!-- REGLA DE SEGURIDAD ABSOLUTA PARA EL FORMULARIO LATERAL -->
        <?php if ($_SESSION['usuario_tipo'] === 'administrador'): ?>
            <!-- COLUMNA IZQUIERDA: Solo se genera en el servidor si el usuario es Administrador -->
            <div class="col-xl-3 col-lg-4">
                <?php require_once 'views/partials/formulario_actividad.php'; ?>
            </div>
        <?php endif; ?>

        <!-- COLUMNA DERECHA: Carga de Trabajo, Filtros y Tabla General -->
        <!-- Ajuste elástico de la rejilla de Bootstrap 5.3: 
             Si es Administrador, comparte pantalla (ocupa 9 de 12 partes). 
             Si es Intermedio o Básico, se expande a pantalla completa (col-12). -->
        <div class="<?php echo ($_SESSION['usuario_tipo'] === 'administrador') ? 'col-xl-9 col-lg-8' : 'col-12'; ?>">
            
            <!-- A. Módulo de Carga de Trabajo Actual por Usuario (Mensual) -->
            <?php require_once 'views/partials/carga_trabajo.php'; ?>

            <!-- B. Módulo de Filtros Avanzados y Tabla Principal de Registros (Paginada) -->
            <?php require_once 'views/partials/tabla_actividades.php'; ?>
            
        </div>

    </div>
</div>

<!-- SCRIPT ASÍNCRONO PARA LA ACTUALIZACIÓN AUTOMÁTICA EN TIEMPO REAL -->
<script>
function actualizarContadoresTiempoReal() {
    // 1. Detectamos los filtros de mes y año presentes en la barra de Bootstrap
    const selectMes  = document.querySelector('select[name="f_mes"]');
    const selectAnio = document.querySelector('select[name="f_anio"]');
    
    const mesActual  = selectMes ? selectMes.value : new Date().getMonth() + 1;
    const anioActual = selectAnio ? selectAnio.value : new Date().getFullYear();

    // 2. REGLA DE SEGURIDAD JS: Evaluamos el rol inyectado desde la sesión de PHP
    const usuarioTipo = "<?php echo $_SESSION['usuario_tipo']; ?>";
    const usuarioId   = "<?php echo $_SESSION['usuario_id']; ?>";
    
    let urlPeticion = 'index.php?accion=get_contadores_ajax&f_mes=' + mesActual + '&f_anio=' + anioActual;
    
    // Si el operador conectado es básico, forzamos a la API a contar únicamente sus registros
    if (usuarioTipo === 'basico') {
        urlPeticion += '&f_usuario_id=' + usuarioId;
    }

    // 3. Ejecutamos el Fetch asíncrono continuo
    fetch(urlPeticion)
        .then(response => {
            if (!response.ok) throw new Error('Error al conectar con la API de contadores');
            return response.json();
        })
        .then(data => {
            if (document.getElementById('kpi-por-asignar')) {
                document.getElementById('kpi-por-asignar').innerText = data.por_asignar;
                document.getElementById('kpi-pendiente').innerText   = data.pendiente;
                document.getElementById('kpi-proceso').innerText     = data.proceso;
                document.getElementById('kpi-finalizado').innerText  = data.finalizado;
            }
        })
        .catch(error => console.error('Error en la sincronización de métricas:', error));
}

// Inicializar de inmediato al cargar la pantalla
actualizarContadoresTiempoReal();

// Consulta automática en segundo plano cada 3 segundos exactos
setInterval(actualizarContadoresTiempoReal, 3000);
</script>


<?php 
// 3. Cargamos el pie de página común que cierra el documento y carga el JS de Bootstrap Local
require_once 'views/partials/footer.php'; 
?>
