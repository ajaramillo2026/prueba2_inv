<?php
require_once 'config/conexion.php';

class TicketModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTiposCatalogo() {
        $query = "SELECT * FROM tipos_ticket ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [CREATE] Guardar ticket incluyendo extensión
    public function guardar($usuario_id, $tipo_ticket_id, $extension) {
        $query = "INSERT INTO tickets (usuario_id, tipo_ticket_id, extension) VALUES (:usuario_id, :tipo_ticket_id, :extension)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':usuario_id'     => (int)$usuario_id,
            ':tipo_ticket_id' => (int)$tipo_ticket_id,
            ':extension'      => trim($extension)
        ]);
    }

    /*// [READ + FILTROS] Obtener listado general bajo los criterios activos
    public function obtenerFiltrados($filtros = []) {
        $sql = "SELECT t.*, u.nombre AS cliente, tp.nombre AS tipo_nombre 
                FROM tickets t 
                JOIN usuarios u ON t.usuario_id = u.id 
                JOIN tipos_ticket tp ON t.tipo_ticket_id = tp.id
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND t.usuario_id = :usuario_id";
            $params[':usuario_id'] = (int)$filtros['usuario_id'];
        }
        if (!empty($filtros['tipo_ticket_id'])) {
            $sql .= " AND t.tipo_ticket_id = :tipo_ticket_id";
            $params[':tipo_ticket_id'] = (int)$filtros['tipo_ticket_id'];
        }
        if (!empty($filtros['mes'])) {
            $sql .= " AND MONTH(t.fecha_creacion) = :mes AND YEAR(t.fecha_creacion) = YEAR(CURRENT_DATE())";
            $params[':mes'] = (int)$filtros['mes'];
        }

        $sql .= " ORDER BY t.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/
    
            // Actualiza este método dentro de models/TicketModel.php
    public function obtenerFiltrados($filtros = []) {
        $sql = "SELECT t.*, u.nombre AS cliente, tp.nombre AS tipo_nombre 
                FROM tickets t 
                JOIN usuarios u ON t.usuario_id = u.id 
                JOIN tipos_ticket tp ON t.tipo_ticket_id = tp.id
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND t.usuario_id = :usuario_id";
            $params[':usuario_id'] = (int)$filtros['usuario_id'];
        }
        if (!empty($filtros['tipo_ticket_id'])) {
            $sql .= " AND t.tipo_ticket_id = :tipo_ticket_id";
            $params[':tipo_ticket_id'] = (int)$filtros['tipo_ticket_id'];
        }
        if (!empty($filtros['mes'])) {
            $sql .= " AND MONTH(t.fecha_creacion) = :mes AND YEAR(t.fecha_creacion) = YEAR(CURRENT_DATE())";
            $params[':mes'] = (int)$filtros['mes'];
        }
        // NUEVO: Filtro dinámico por extensión telefónica
        if (!empty($filtros['extension'])) {
            $sql .= " AND t.extension LIKE :extension";
            $params[':extension'] = '%' . trim($filtros['extension']) . '%';
        }

        $sql .= " ORDER BY t.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Consulta de contadores para la API en tiempo real
    public function obtenerMétricasTiempoReal($usuario_id = null) {
        $params = [];
        $condicion_usuario = "";
        if ($usuario_id !== null) {
            $condicion_usuario = " AND usuario_id = :usuario_id";
            $params[':usuario_id'] = (int)$usuario_id;
        }

        $query = "SELECT 
                    SUM(CASE WHEN DATE(fecha_creacion) = CURRENT_DATE() THEN 1 ELSE 0 END) as hoy,
                    SUM(CASE WHEN fecha_creacion >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as semana,
                    SUM(CASE WHEN MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as mes
                  FROM tickets WHERE 1=1" . $condicion_usuario;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'hoy'    => (int)($res['hoy'] ?? 0),
            'semana' => (int)($res['semana'] ?? 0),
            'mes'    => (int)($res['mes'] ?? 0)
        ];
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM tickets WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [UPDATE] Actualizar ticket incluyendo extensión
    public function actualizar($id, $usuario_id, $tipo_ticket_id, $extension) {
        $query = "UPDATE tickets SET usuario_id = :usuario_id, tipo_ticket_id = :tipo_ticket_id, extension = :extension WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':usuario_id'     => (int)$usuario_id,
            ':tipo_ticket_id' => (int)$tipo_ticket_id,
            ':extension'      => trim($extension),
            ':id'              => (int)$id
        ]);
    }

    public function eliminar($id) {
        $query = "DELETE FROM tickets WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => (int)$id]);
    }

    
}
