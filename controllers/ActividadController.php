<?php
require_once 'models/ActividadModel.php';
require_once 'models/UsuarioModel.php';

class ActividadController {
    private $modeloAct;
    private $modeloUser;

    public function __construct() {
        // Inicialización de los modelos de datos en el patrón MVC
        $this->modeloAct = new ActividadModel();
        $this->modeloUser = new UsuarioModel();
    }

    // Método principal: Renderiza el panel unificado (KPIs, Carga de Trabajo por Tipo, Filtros y Tabla)
     public function index() {
        // 1. Capturar filtros de tiempo corporativos
        $mes_seleccionado  = isset($_GET['f_mes']) && $_GET['f_mes'] !== '' ? (int)$_GET['f_mes'] : (int)date('n');
        $anio_seleccionado = isset($_GET['f_anio']) && $_GET['f_anio'] !== '' ? (int)$_GET['f_anio'] : (int)date('Y');
        
        $registros_por_pagina = isset($_GET['f_limite']) && $_GET['f_limite'] !== '' ? (int)$_GET['f_limite'] : 10;
        $pagina_actual        = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
        $inicio_limit         = ($pagina_actual - 1) * $registros_por_pagina;

        // CAPA DE SEGURIDAD OPERATIVA: El rol básico queda amarrado a ver solo sus folios asignados
        if ($_SESSION['usuario_tipo'] === 'basico') {
            $responsable_filtro = $_SESSION['usuario_id'];
        } else {
            $responsable_filtro = isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] !== '' ? (int)$_GET['f_usuario_id'] : '';
        }

        // CAPTURA ESTRICTA DEL ESTATUS DE BÚSQUEDA
        $estatus_busqueda = $_GET['f_status'] ?? '';

        $filtros = [
            'tipo'        => $_GET['f_tipo'] ?? '',
            'status'      => $estatus_busqueda, // Inyectamos el filtro de estatus a la consulta SQL
            'medio'       => $_GET['f_medio'] ?? '',
            'usuario_id'  => $responsable_filtro,
            'mes'         => $mes_seleccionado,
            'anio'        => $anio_seleccionado,
            'fecha_desde' => $_GET['f_fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['f_fecha_hasta'] ?? ''
        ];

        // 2. Obtener el universo de registros que cumplen estrictamente con TODOS los filtros elegidos
        $todasLasFiltradas = $this->modeloAct->obtenerFiltradas($filtros);
        $total_registros   = count($todasLasFiltradas);
        $total_paginas     = ($total_registros > 0) ? ceil($total_registros / $registros_por_pagina) : 1;

        // DataTables ordenará localmente, alimentamos el set filtrado por el backend
        $actividades = $todasLasFiltradas;
        $usuarios = $this->modeloUser->obtenerTodos();
        
        // 3. Inicializar estructura de contadores KPI superiores
        $kpi = [
            'por_asignar' => 0, 'pendiente' => 0, 'proceso' => 0, 'finalizado' => 0, 'periodo_nombre' => ''
        ];
        $meses_espanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $kpi['periodo_nombre'] = $meses_espanol[$mes_seleccionado] . " " . $anio_seleccionado;

        // CALCULADORA DE KPIs CONTROLADA POR PRIVILEGIOS DE ROL
        foreach ($todasLasFiltradas as $a) {
            if ($_SESSION['usuario_tipo'] === 'basico' && $a['usuario_id'] != $_SESSION['usuario_id']) {
                continue;
            }
            if ($a['status'] == 'por asignar')  $kpi['por_asignar']++;
            if ($a['status'] == 'pendiente')    $kpi['pendiente']++;
            if ($a['status'] == 'proceso')      $kpi['proceso']++;
            if ($a['status'] == 'finalizado')   $kpi['finalizado']++;
        }

            // 5. PROCESADOR DE CARGA DE TRABAJO CON DESGLOSE DE SUB-ESTADOS Y TOTALES POR TIPO
        $carga_trabajo = [];
        foreach ($usuarios as $u) {
            $carga_trabajo[$u['id']] = [
                'nombre' => $u['nombre'],
                'rol'    => $u['tipo_usuario'],
                
                // ACTIVIDADES: Sub-estados + TOTAL TIPO
                'act_pendiente'   => 0, 'act_proceso' => 0, 'act_finalizado' => 0, 'act_total' => 0,
                
                // REQUERIMIENTOS: Sub-estados + TOTAL TIPO
                'req_pendiente'   => 0, 'req_proceso' => 0, 'req_finalizado' => 0, 'req_total' => 0,
                
                // HALLAZGOS: Sub-estados + TOTAL TIPO
                'hal_pendiente'   => 0, 'hal_proceso' => 0, 'hal_finalizado' => 0, 'hal_total' => 0,
                
                // Acumuladores Macro de Control
                'total_activo'    => 0, // Pendientes + Proceso (Para el semáforo)
                'total_concluido' => 0  // Finalizados
            ];
        }

        foreach ($todasLasFiltradas as $am) {
            if (!empty($am['usuario_id']) && isset($carga_trabajo[$am['usuario_id']])) {
                $tipo   = strtolower(trim($am['tipo']));
                $status = strtolower(trim($am['status']));
                $id_u   = $am['usuario_id'];

                // 1. ACTIVIDADES: Conteo de sub-estados y acumulador total
                if ($tipo === 'actividad') {
                    if ($status === 'pendiente')   $carga_trabajo[$id_u]['act_pendiente']++;
                    if ($status === 'proceso')     $carga_trabajo[$id_u]['act_proceso']++;
                    if ($status === 'finalizado')  $carga_trabajo[$id_u]['act_finalizado']++;
                    $carga_trabajo[$id_u]['act_total']++; // Total absoluto del tipo
                }

                // 2. REQUERIMIENTOS: Conteo de sub-estados y acumulador total
                if ($tipo === 'requerimiento') {
                    if ($status === 'pendiente')   $carga_trabajo[$id_u]['req_pendiente']++;
                    if ($status === 'proceso')     $carga_trabajo[$id_u]['req_proceso']++;
                    if ($status === 'finalizado')  $carga_trabajo[$id_u]['req_finalizado']++;
                    $carga_trabajo[$id_u]['req_total']++; // Total absoluto del tipo
                }

                // 3. HALLAZGOS: Conteo de sub-estados y acumulador total
                if ($tipo === 'hallazgos') {
                    if ($status === 'pendiente')   $carga_trabajo[$id_u]['hal_pendiente']++;
                    if ($status === 'proceso')     $carga_trabajo[$id_u]['hal_proceso']++;
                    if ($status === 'finalizado')  $carga_trabajo[$id_u]['hal_finalizado']++;
                    $carga_trabajo[$id_u]['hal_total']++; // Total absoluto del tipo
                }

                // 4. ACUMULADORES MACRO (CONTROL DE AVANCE)
                if ($status === 'pendiente' || $status === 'proceso' || $status === 'por asignar') {
                    $carga_trabajo[$id_u]['total_activo']++;
                } elseif ($status === 'finalizado') {
                    $carga_trabajo[$id_u]['total_concluido']++;
                }
            }
        }

        // Ordenación de mayor a menor concentración de folios en curso
        uasort($carga_trabajo, function($a, $b) { 
            return $b['total_activo'] <=> $a['total_activo']; 
        });

        // Invocación a la vista HTML principal
        require_once 'views/actividades_view.php';
    }



    // Procesa el alta de folios (Disponible solo para Administradores)
    public function guardar() {
        if ($_SESSION['usuario_tipo'] !== 'administrador') {
            header("Location: index.php?accion=dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->modeloAct->guardar(
                $_POST['titulo'], $_POST['asunto'], $_POST['descripcion'],
                $_POST['tipo'], $_POST['solicitante'], $_POST['medio'],
                $_POST['vobo_nya'], $_POST['usuario_id']
            );
        }
        header("Location: index.php?accion=dashboard");
        exit;
    }

    // Procesa el cambio de estatus y responsable de la tabla general
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_actividad = (int)$_POST['id'];
            $nuevo_status = $_POST['status'];
            
            // CANDADO BACKEND: El rol básico no puede asignar folios a otros compañeros
            if ($_SESSION['usuario_tipo'] === 'basico') {
                $responsable_final = (int)$_SESSION['usuario_id'];
            } else {
                $responsable_final = isset($_POST['usuario_id']) && $_POST['usuario_id'] !== '' ? (int)$_POST['usuario_id'] : null;
            }

            $this->modeloAct->actualizarEstadoOAsignacion($id_actividad, $nuevo_status, $responsable_final);
        }
        header("Location: index.php?accion=dashboard");
        exit;
    }

    // Procesa la edición del contenido de texto (Modal - Exclusivo Administrador)
    public function editarContenido() {
        if ($_SESSION['usuario_tipo'] !== 'administrador') {
            header("Location: index.php?accion=dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->modeloAct->editarContenidoCompleto(
                $_POST['id'], $_POST['titulo'], $_POST['asunto'], $_POST['descripcion'],
                $_POST['tipo'], $_POST['medio'], $_POST['solicitante'], $_POST['vobo_nya']
            );
        }
        header("Location: index.php?accion=dashboard");
        exit;
    }

    // Procesa la eliminación física de un registro (Exclusivo Administrador)
    public function eliminar() {
        if ($_SESSION['usuario_tipo'] !== 'administrador') {
            header("Location: index.php?accion=dashboard");
            exit;
        }

        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $this->modeloAct->eliminarActividad((int)$_GET['id']);
        }
        header("Location: index.php?accion=dashboard");
        exit;
    }

    // API Asíncrona (AJAX): CORREGIDO con flecha asociativa (=>) para evitar errores del sistema
    public function obtenerContadoresAJAX() {
        $mes_evaluar  = isset($_GET['f_mes']) && $_GET['f_mes'] !== '' ? (int)$_GET['f_mes'] : (int)date('n');
        $anio_evaluar = isset($_GET['f_anio']) && $_GET['f_anio'] !== '' ? (int)$_GET['f_anio'] : (int)date('Y');

        if ($_SESSION['usuario_tipo'] === 'basico') {
            $responsable_ajax = $_SESSION['usuario_id'];
        } else {
            $responsable_ajax = isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] !== '' ? (int)$_GET['f_usuario_id'] : '';
        }

        $kpi = [
            'por_asignar' => 0, 'pendiente' => 0, 'proceso' => 0, 'finalizado' => 0
        ];

        // CORREGIDO: Se incluyó el operador asociativo '=>' de manera explícita
        $todas = $this->modeloAct->obtenerFiltradas([
            'mes'        => $mes_evaluar, 
            'anio'       => $anio_evaluar,
            'usuario_id' => $responsable_ajax
        ]); 
        
        foreach ($todas as $a) {
            if ($a['status'] == 'por asignar')  $kpi['por_asignar']++;
            if ($a['status'] == 'pendiente')    $kpi['pendiente']++;
            if ($a['status'] == 'proceso')      $kpi['proceso']++;
            if ($a['status'] == 'finalizado')   $kpi['finalizado']++;
        }

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($kpi);
        exit;
    }

    // Genera la descarga del archivo Excel respetando los filtros de la barra
    public function exportarExcel() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            $responsable_excel = $_SESSION['usuario_id'];
        } else {
            $responsable_excel = isset($_GET['f_usuario_id']) && $_GET['f_usuario_id'] !== '' ? (int)$_GET['f_usuario_id'] : '';
        }

        $filtros = [
            'tipo'        => $_GET['f_tipo'] ?? '',
            'status'      => $_GET['f_status'] ?? '',
            'medio'       => $_GET['f_medio'] ?? '',
            'usuario_id'  => $responsable_excel,
            'mes'         => $_GET['f_mes'] ?? date('n'),
            'anio'        => $_GET['f_anio'] ?? date('Y'),
            'fecha_desde' => $_GET['f_fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['f_fecha_hasta'] ?? ''
        ];
        
        $data = $this->modeloAct->obtenerFiltradas($filtros);

        // Forzar cabeceras del protocolo HTTP para descarga directa del fichero XLS
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Actividades.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Estructuración HTML interpretada de forma nativa por Microsoft Excel
        echo "<table border='1'>";
        echo "<tr style='background-color: #111; color: #FFF; font-weight: bold;'>
                <th>ID</th>
                <th>Titulo</th>
                <th>Asunto</th>
                <th>Tipo</th>
                <th>Solicitante</th>
                <th>Medio</th>
                <th>VoBo NYA</th>
                <th>Estatus</th>
                <th>Asignado A</th>
                <th>Fecha Creacion</th>
                <th>Fecha Finalizacion</th>
              </tr>";
              
        foreach ($data as $row) {
            $encargado = $row['encargado'] ?? 'Sin Asignar';
            $fecha_fin = $row['fecha_finalizacion'] ?? 'En curso';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['titulo']) . "</td>
                    <td>" . htmlspecialchars($row['asunto']) . "</td>
                    <td>" . ucfirst(htmlspecialchars($row['tipo'])) . "</td>
                    <td>" . htmlspecialchars($row['solicitante']) . "</td>
                    <td>" . ucfirst(htmlspecialchars($row['medio'])) . "</td>
                    <td>" . strtoupper($row['vobo_nya']) . "</td>
                    <td>" . ucwords($row['status']) . "</td>
                    <td>" . htmlspecialchars($encargado) . "</td>
                    <td>{$row['fecha_creacion']}</td>
                    <td>{$fecha_fin}</td>
                  </tr>";
        }
        echo "</table>";
        exit;
    }
}
