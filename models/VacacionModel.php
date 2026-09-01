<?php
require_once 'config/conexion.php';

class VacacionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // Calcula los días que le corresponden por ley según sus años trabajados
    public function calcularDiasPorAntiguedad($anios) {
        if ($anios < 1) return 0;
        if ($anios == 1) return 12;
        if ($anios == 2) return 14;
        if ($anios == 3) return 16;
        if ($anios == 4) return 18;
        if ($anios == 5) return 20;
        if ($anios >= 6 && $anios <= 10) return 22;
        if ($anios >= 11 && $anios <= 15) return 24;
        if ($anios >= 16 && $anios <= 20) return 26;
        return 28; // 21 a 25 años
    }

    // Obtiene el expediente de días del usuario (Totales por ley, Consumidos y Disponibles)
    public function obtenerExpedienteVacaciones($usuario_id) {
        // 1. Traer la fecha de ingreso del usuario
        $queryUser = "SELECT fecha_ingreso FROM usuarios WHERE id = :id";
        $stmtUser = $this->db->prepare($queryUser);
        $stmtUser->execute([':id' => (int)$usuario_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) return ['ley' => 0, 'consumidos' => 0, 'disponibles' => 0, 'anios' => 0];

        // 2. Calcular años cumplidos a la fecha actual (2026)
        $ingreso = new DateTime($user['fecha_ingreso']);
        $hoy = new DateTime(date('Y-m-d'));
        $antiguedad = $hoy->diff($ingreso);
        $anios_trabajados = $antiguedad->y;

        $dias_derecho_ley = $this->calcularDiasPorAntiguedad($anios_trabajados);

        // 3. Contar días ya tomados o apartados en el sistema (Aprobados o Pendientes)
        $queryDias = "SELECT SUM(dias_solicitados) as consumidos 
                      FROM vacaciones 
                      WHERE usuario_id = :usuario_id AND estatus != 'rechazado'";
        $stmtDias = $this->db->prepare($queryDias);
        $stmtDias->execute([':usuario_id' => (int)$usuario_id]);
        $resDias = $stmtDias->fetch(PDO::FETCH_ASSOC);
        
        $dias_consumidos = (int)($resDias['consumidos'] ?? 0);
        $dias_disponibles = $dias_derecho_ley - $dias_consumidos;

        return [
            'ley'         => $dias_derecho_ley,
            'consumidos'  => $dias_consumidos,
            'disponibles' => max(0, $dias_disponibles),
            'anios'       => $anios_trabajados
        ];
    }

    public function guardar($usuario_id, $fecha_inicio, $fecha_fin, $dias, $estatus, $observaciones) {
        $query = "INSERT INTO vacaciones (usuario_id, fecha_inicio, fecha_fin, dias_solicitados, estatus, observaciones) 
                  VALUES (:usuario_id, :fecha_inicio, :fecha_fin, :dias, :estatus, :observaciones)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':usuario_id'   => (int)$usuario_id,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin'    => $fecha_fin,
            ':dias'         => (int)$dias,
            ':estatus'      => $estatus,
            ':observaciones'=> !empty($observaciones) ? trim($observaciones) : null
        ]);
    }

    public function obtenerFiltradas($filtros = []) {
        $sql = "SELECT v.*, u.nombre AS empleado, u.fecha_ingreso 
                FROM vacaciones v 
                JOIN usuarios u ON v.usuario_id = u.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND v.usuario_id = :usuario_id";
            $params[':usuario_id'] = (int)$filtros['usuario_id'];
        }
        if (!empty($filtros['estatus'])) {
            $sql .= " AND v.estatus = :estatus";
            $params[':estatus'] = $filtros['estatus'];
        }

        $sql .= " ORDER BY v.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM vacaciones WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $query = "DELETE FROM vacaciones WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => (int)$id]);
    }
}
