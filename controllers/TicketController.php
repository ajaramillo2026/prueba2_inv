<?php
require_once 'models/TicketModel.php';
require_once 'models/UsuarioModel.php';

class TicketController {
    private $modeloTicket;
    private $modeloUser;

    public function __construct() {
        $this->modeloTicket = new TicketModel();
        $this->modeloUser   = new UsuarioModel();
    }

        // Actualiza el inicio del método index() en controllers/TicketController.php
    public function index() {
        $mes_seleccionado = isset($_GET['f_mes']) && $_GET['f_mes'] !== '' ? (int)$_GET['f_mes'] : (int)date('n');

        // PARÁMETROS DE PAGINACIÓN DINÁMICA
        $registros_por_pagina = isset($_GET['f_limite']) && $_GET['f_limite'] !== '' ? (int)$_GET['f_limite'] : 10;
        $pagina_actual        = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
        $inicio_limit         = ($pagina_actual - 1) * $registros_por_pagina;

        if ($_SESSION['usuario_tipo'] === 'basico') {
            $responsable_filtro = $_SESSION['usuario_id'];
            $kpis_iniciales = $this->modeloTicket->obtenerMétricasTiempoReal($_SESSION['usuario_id']);
        } else {
            $responsable_filtro = isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] !== '' ? (int)$_GET['f_usuario_id'] : '';
            $kpis_iniciales = $this->modeloTicket->obtenerMétricasTiempoReal(null);
        }

        $filtros = [
            'usuario_id'     => $responsable_filtro,
            'tipo_ticket_id' => isset($_GET['f_tipo_ticket_id']) && $_GET['f_tipo_ticket_id'] !== '' ? (int)$_GET['f_tipo_ticket_id'] : '',
            'mes'            => $mes_seleccionado,
            'extension'      => $_GET['f_extension'] ?? ''
        ];

        // 1. Obtener el universo TOTAL de registros bajo los filtros activos (para la paginación y carga de trabajo)
        $todosLosTicketsFiltrados = $this->modeloTicket->obtenerFiltrados($filtros);
        $total_registros_tickets  = count($todosLosTicketsFiltrados);
        $total_paginas_tickets    = ($total_registros_tickets > 0) ? ceil($total_registros_tickets / $registros_por_pagina) : 1;

        // 2. Recortar manualmente o pasar los límites para la visualización paginada en el historial
        // Nota: Si tu obtenerFiltrados() no admite limit/offset en BD, recortamos el array nativo de PHP:
        $tickets = array_slice($todosLosTicketsFiltrados, $inicio_limit, $registros_por_pagina);
        
        $usuarios       = $this->modeloUser->obtenerTodos();
        $tipos_catalogo = $this->modeloTicket->obtenerTiposCatalogo();
        
        // --- PROCESADOR DE CARGA DE TRABAJO DINÁMICA (Sobre el total filtrado) ---
        $carga_tickets = [];
        foreach ($usuarios as $u) {
            $carga_tickets[$u['id']] = [
                'nombre' => $u['nombre'], 'rol' => $u['tipo_usuario'],
                'hoy' => 0, 'semana' => 0, 'mes' => 0
            ];
        }

        $hoy_inicio   = strtotime(date('Y-m-d 00:00:00'));
        $semana_limite = strtotime('-7 days 00:00:00');
        $mes_inicio   = strtotime(date('Y-m-01 00:00:00'));

        foreach ($todosLosTicketsFiltrados as $t) {
            $id_encargado = $t['usuario_id'];
            $fecha_ticket = strtotime($t['fecha_creacion']);

            if (isset($carga_tickets[$id_encargado])) {
                if ($fecha_ticket >= $hoy_inicio)      $carga_tickets[$id_encargado]['hoy']++;
                if ($fecha_ticket >= $semana_limite)  $carga_tickets[$id_encargado]['semana']++;
                if ($fecha_ticket >= $mes_inicio)     $carga_tickets[$id_encargado]['mes']++;
            }
        }

        uasort($carga_tickets, function($a, $b) { return $b['mes'] <=> $a['mes']; });

        $ticketEditar = null;
        if (isset($_GET['editar_id'])) {
            $ticketEditar = $this->modeloTicket->obtenerPorId($_GET['editar_id']);
        }
        require_once 'views/tickets_view.php';
    }



    public function guardar() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=dashboard");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id             = $_POST['id'] ?? null;
            $usuario_id     = $_POST['usuario_id'];
            $tipo_ticket_id = $_POST['tipo_ticket_id'];
            $extension      = $_POST['extension']; // Captura extensión

            if ($id) {
                $this->modeloTicket->actualizar($id, $usuario_id, $tipo_ticket_id, $extension);
            } else {
                $this->modeloTicket->guardar($usuario_id, $tipo_ticket_id, $extension);
            }
        }
        header("Location: index.php?accion=tickets");
        exit;
    }

    public function eliminar() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=dashboard");
            exit;
        }
        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $this->modeloTicket->eliminar((int)$_GET['id']);
        }
        header("Location: index.php?accion=tickets");
        exit;
    }

    public function contadoresTicketsAJAX() {
        $id_usuario_evaluar = ($_SESSION['usuario_tipo'] === 'basico') ? $_SESSION['usuario_id'] : null;
        $datos_kpi = $this->modeloTicket->obtenerMétricasTiempoReal($id_usuario_evaluar);
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos_kpi);
        exit;
    }
}
