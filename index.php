<?php
// ==========================================================================
// CONFIGURACIÓN GLOBAL DEL FRONT CONTROLLER - ENTORNO DE DESARROLLO 2026
// ==========================================================================

// Forzar al servidor local a mostrar cualquier error interno de sintaxis o PDO
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciamos la sesión global de PHP para almacenar y validar tokens de operadores
session_start();

// Importamos de manera modular todos los controladores del ecosistema MVC
require_once 'controllers/LoginController.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/ActividadController.php';
require_once 'controllers/TicketController.php';
require_once 'controllers/VacacionController.php';
require_once 'controllers/InventarioController.php'; // INTEGRACIÓN DE INVENTARIO TI

// Instanciamos los objetos controladores para invocar sus métodos de negocio
$loginCtrl     = new LoginController();
$usuarioCtrl   = new UsuarioController();
$actividadCtrl = new ActividadController();
$ticketCtrl    = new TicketController();
$vacacionCtrl  = new VacacionController();
$inventarioCtrl = new InventarioController(); // INSTANCIA DE INVENTARIO TI

// Capturamos la acción solicitada por la URL (si no existe, por defecto va al dashboard principal)
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'dashboard';

// ==========================================================================
// --- SISTEMA DE PROTECCIÓN DE RUTAS / MIDDLEWARE DE SEGURIDAD INTERNO ---
// ==========================================================================
$accionesPublicas = ['login', 'procesar_login'];

// Si no hay una sesión activa en el servidor y la acción no es pública, deniega el acceso
if (!isset($_SESSION['usuario_id']) && !in_array($accion, $accionesPublicas)) {
    
    // CORTOCIRCUITO ASÍNCRONO: Si es una petición Fetch/AJAX de fondo, responde JSON limpio en lugar de HTML pesado
    $accionesAsincronas = ['get_tickets_ajax', 'get_contadores_ajax'];
    if (in_array($accion, $accionesAsincronas)) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Sesion expirada en el servidor. Reautentique.']);
        exit;
    }
    
    // Si es una navegación ordinaria por pantalla, lo rebota al formulario de acceso
    header("Location: index.php?accion=login");
    exit;
}

// ==========================================================================
// --- ENRUTADOR CENTRAL DE ACCIONES (SWITCH ROUTER PRINCIPAL) ---
// ==========================================================================
switch ($accion) {
    
    // ----------------------------------------------------------------------
    // 1. RUTAS DEL MÓDULO DE AUTENTICACIÓN (LOGIN & LOGOUT)
    // ----------------------------------------------------------------------
    case 'login':
        $loginCtrl->mostrarLogin();
        break;

    case 'procesar_login':
        $loginCtrl->procesarLogin();
        break;

    case 'logout':
        $loginCtrl->cerrarSesion();
        break;

    // ----------------------------------------------------------------------
    // 2. RUTAS DEL MÓDULO DE USUARIOS Y PERSONAL (CRUD & PRIVACIDAD)
    // ----------------------------------------------------------------------
    case 'usuarios':
        $usuarioCtrl->index();
        break;

    case 'guardar_usuario':
        $usuarioCtrl->guardar();
        break;

    case 'eliminar_usuario':
        $usuarioCtrl->eliminar();
        break;

    case 'cambiar_password_usuario':
        $usuarioCtrl->cambiarPassword();
        break;

    // ----------------------------------------------------------------------
    // 3. RUTAS DEL MÓDULO DE PERFIL PRIVADO AUTÓNOMO
    // ----------------------------------------------------------------------
    case 'mi_perfil':
        $loginCtrl->miPerfil();
        break;

    case 'actualizar_mis_datos':
        $loginCtrl->actualizarMisDatos();
        break;

    case 'actualizar_mi_password':
        $loginCtrl->actualizarMiPassword();
        break;

    // ----------------------------------------------------------------------
    // 4. RUTAS DEL MÓDULO DE ACTIVIDADES, REQUERIMIENTOS Y REPORTES
    // ----------------------------------------------------------------------
    case 'guardar_actividad':
        $actividadCtrl->guardar();
        break;

    case 'actualizar_actividad':
        $actividadCtrl->actualizar();
        break;

    case 'editar_contenido_actividad':
        $actividadCtrl->editarContenido();
        break;

    case 'eliminar_actividad':
        $actividadCtrl->eliminar();
        break;

    case 'get_contadores_ajax':
        $actividadCtrl->obtenerContadoresAJAX();
        break;

    case 'exportar':
        $actividadCtrl->exportarExcel();
        break;

    // ----------------------------------------------------------------------
    // 5. RUTAS DEL MÓDULO DE TICKETS DE SOPORTE RELACIONALES
    // ----------------------------------------------------------------------
    case 'tickets':
        $ticketCtrl->index();
        break;

    case 'guardar_ticket':
        $ticketCtrl->guardar();
        break;

    case 'eliminar_ticket':
        $ticketCtrl->eliminar();
        break;
    
    case 'get_tickets_ajax':
        $ticketCtrl->contadoresTicketsAJAX();
        break;

    // ----------------------------------------------------------------------
    // 6. RUTAS DEL MÓDULO DE VACACIONES DIGNAS (LEY MEXICANA LFT)
    // ----------------------------------------------------------------------
    case 'vacaciones':
        $vacacionCtrl->index();
        break;

    case 'guardar_vacacion':
        $vacacionCtrl->guardar();
        break;

    case 'eliminar_vacacion':
        $vacacionCtrl->eliminar();
        break;

    // ----------------------------------------------------------------------
    // 7. RUTAS DEL MÓDULO DE INVENTARIO TECNOLÓGICO CON CARGAS CSV
    // ----------------------------------------------------------------------
    case 'inventario':
        $inventarioCtrl->index();
        break;

    case 'guardar_inventario':
        $inventarioCtrl->guardar();
        break;

    case 'descontar_stock_inventario':
        $inventarioCtrl->registrarConsumo();
        break;

    case 'eliminar_inventario':
        $inventarioCtrl->eliminar();
        break;

    case 'importar_inventario_csv':
        $inventarioCtrl->importarCSV();
        break;

    case 'exportar_inventario':
        $inventarioCtrl->exportarExcel();
        break;

    // ----------------------------------------------------------------------
    // RUTA CONTRAPESO / DETERMINISTA PREDETERMINADA
    // ----------------------------------------------------------------------
    case 'dashboard':
    default:
        $actividadCtrl->index();
        break;
}
