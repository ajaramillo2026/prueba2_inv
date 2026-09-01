<?php
require_once 'models/VacacionModel.php';
require_once 'models/UsuarioModel.php';

class VacacionController {
    private $modeloVac;
    private $modeloUser;

    public function __construct() {
        $this->modeloVac  = new VacacionModel();
        $this->modeloUser = new UsuarioModel();
    }

    public function index() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            $responsable_filtro = $_SESSION['usuario_id'];
        } else {
            $responsable_filtro = isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] !== '' ? (int)$_GET['f_usuario_id'] : '';
        }

        $filtros = [
            'usuario_id' => $responsable_filtro,
            'estatus'    => $_GET['f_estatus'] ?? ''
        ];

        $vacaciones = $this->modeloVac->obtenerFiltradas($filtros);
        
        // Conseguimos todos los usuarios con sus metadatos e inyectamos sus saldos LFT vigentes
        $usuarios_crudo = $this->modeloUser->obtenerTodos();
        $usuarios = [];
        foreach ($usuarios_crudo as $u) {
            $expediente = $this->modeloVac->obtenerExpedienteVacaciones($u['id']);
            $u['dias_disponibles'] = $expediente['disponibles'];
            $u['anios_cumplidos']  = $expediente['anios'];
            $usuarios[] = $u;
        }

        // Si el usuario es básico, cargamos sus contadores individuales en pantalla
        $mi_expediente = $this->modeloVac->obtenerExpedienteVacaciones($_SESSION['usuario_id']);

        $vacacionEditar = null;
        if (isset($_GET['editar_id'])) {
            $vacacionEditar = $this->modeloVac->obtenerPorId($_GET['editar_id']);
        }

        require_once 'views/vacaciones_view.php';
    }

    public function guardar() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario_id    = (int)$_POST['usuario_id'];
            $fecha_inicio  = $_POST['fecha_inicio'];
            $fecha_fin     = $_POST['fecha_fin'];
            $estatus       = $_POST['estatus'];
            $observaciones = $_POST['observaciones'];

            // 1. Calcular días naturales solicitados por el operador
            $dias = ((strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400) + 1;

            if ($dias <= 0) {
                $_SESSION['error_vacaciones'] = "❌ La fecha de finalización no puede ser menor a la inicial.";
                header("Location: index.php?accion=vacaciones");
                exit;
            }

            // 2. CANDADO DE BACKEND: Re-verificar saldo disponible real en la Base de Datos
            $expediente = $this->modeloVac->obtenerExpedienteVacaciones($usuario_id);
            
            if ($dias > $expediente['disponibles']) {
                $_SESSION['error_vacaciones'] = "🚫 Operación Denegada: El usuario solo cuenta con " . $expediente['disponibles'] . " días disponibles. Intentaste registrar " . $dias . " días.";
                header("Location: index.php?accion=vacaciones");
                exit;
            }

            // 3. Guardar si pasa el candado
            $this->modeloVac->guardar($usuario_id, $fecha_inicio, $fecha_fin, $dias, $estatus, $observaciones);
            $_SESSION['exito_vacaciones'] = "📅 Período de vacaciones registrado exitosamente.";
        }
        header("Location: index.php?accion=vacaciones");
        exit;
    }

    public function eliminar() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=dashboard");
            exit;
        }
        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $this->modeloVac->eliminar((int)$_GET['id']);
        }
        header("Location: index.php?accion=vacaciones");
        exit;
    }
}
