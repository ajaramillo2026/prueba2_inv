<?php
require_once 'config/conexion.php';

class ActividadModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // [CREATE] Registrar actividad (calcula estatus automáticamente si lleva usuario)
    public function guardar($titulo, $asunto, $descripcion, $tipo, $solicitante, $medio, $vobo_nya, $usuario_id) {
        $user_val = (!empty($usuario_id)) ? $usuario_id : null;
        $status = ($user_val) ? 'pendiente' : 'por asignar';

        $query = "INSERT INTO actividades (titulo, asunto, descripcion, tipo, solicitante, medio, vobo_nya, status, usuario_id) 
                  VALUES (:titulo, :asunto, :descripcion, :tipo, :solicitante, :medio, :vobo_nya, :status, :usuario_id)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':titulo' => $titulo, 
            ':asunto' => $asunto, 
            ':descripcion' => $descripcion,
            ':tipo' => $tipo, 
            ':solicitante' => $solicitante, 
            ':medio' => $medio,
            ':vobo_nya' => $vobo_nya, 
            ':status' => $status,
            ':usuario_id' => $user_val
        ]);
    }

    /*/ [READ + FILTROS] Obtener actividades según los criterios del usuario
    public function obtenerFiltradas($filtros = []) {
        $sql = "SELECT a.*, u.nombre AS encargado FROM actividades a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND a.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $filtros['status'];
        }
        if (!empty($filtros['medio'])) {
            $sql .= " AND a.medio = :medio";
            $params[':medio'] = $filtros['medio'];
        }

        $sql .= " ORDER BY a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/

    
    public function obtenerFiltradas($filtros = []) {
        $sql = "SELECT a.*, u.nombre AS encargado FROM actividades a 
                LEFT JOIN usuarios u ON a.usuario_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND a.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $filtros['status'];
        }
        if (!empty($filtros['medio'])) {
            $sql .= " AND a.medio = :medio";
            $params[':medio'] = $filtros['medio'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND a.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }
        // NUEVO: Filtro por número de mes (Aplica para el año actual)
        /*if (!empty($filtros['mes'])) {
            $sql .= " AND MONTH(a.fecha_creacion) = :mes AND YEAR(a.fecha_creacion) = YEAR(CURRENT_DATE())";
            $params[':mes'] = $filtros['mes'];
        }*/
        
        if (!empty($filtros['mes'])) {
            $sql .= " AND MONTH(a.fecha_creacion) = :mes";
            $params[':mes'] = $filtros['mes'];
            
            // Si se definió un año en el filtro, lo inyectamos de forma dinámica
            if (!empty($filtros['anio'])) {
                $sql .= " AND YEAR(a.fecha_creacion) = :anio";
                $params[':anio'] = $filtros['anio'];
            } else {
                // Si no hay año seleccionado, por defecto toma el año en curso
                $sql .= " AND YEAR(a.fecha_creacion) = YEAR(CURRENT_DATE())";
            }
        }


        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(a.fecha_creacion) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(a.fecha_creacion) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY a.id DESC";
        
        if (isset($filtros['limit']) && isset($filtros['offset'])) {
            // Inyectamos numéricamente los valores de manera directa y segura mediante casteo explícito
            $limit = (int)$filtros['limit'];
            $offset = (int)$filtros['offset'];
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $sql .= ""; // (Continúa el $stmt = $this->db->prepare($sql); exactamente igual)

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function obtenerActividadesMesActual() {
        $query = "SELECT * FROM actividades 
                  WHERE YEAR(fecha_creacion) = YEAR(CURRENT_DATE()) 
                    AND MONTH(fecha_creacion) = MONTH(CURRENT_DATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [UPDATE] SOLUCIONADO: Cambio de estado, asignación y fecha_finalizacion sin errores de tokens
    public function actualizarEstadoOAsignacion($id, $status, $usuario_id) {
        $user_val = (!empty($usuario_id)) ? $usuario_id : null;
        
        // Si se le asigna un usuario y seguía "por asignar", cambia a "pendiente" automáticamente
        if ($user_val && $status == 'por asignar') {
            $status = 'pendiente';
        }

        // Corrección: Usamos dos veces el token :status_eval para evitar duplicidades que confundan a PDO
        $query = "UPDATE actividades SET 
                    status = :status, 
                    usuario_id = :usuario_id,
                    fecha_finalizacion = CASE 
                        WHEN :status_eval = 'finalizado' THEN NOW() 
                        ELSE NULL 
                    END
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        
        // El número de elementos aquí adentro ahora coincide exactamente con los tokens del SQL de arriba
        return $stmt->execute([
            ':status'       => $status, 
            ':usuario_id'   => $user_val, 
            ':status_eval'  => $status, 
            ':id'           => $id
        ]);
    }

    public function eliminarActividad($id) {
        $query = "DELETE FROM actividades WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => (int)$id]);
    }
    
        // NUEVO MÉTODO: Actualizar campos de texto de la actividad (Solo administradores)
    public function editarContenidoCompleto($id, $titulo, $asunto, $descripcion, $tipo, $medio, $solicitante, $vobo_nya) {
        $query = "UPDATE actividades SET 
                    titulo = :titulo, asunto = :asunto, descripcion = :descripcion,
                    tipo = :tipo, medio = :medio, solicitante = :solicitante, vobo_nya = :vobo_nya 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':titulo'      => trim($titulo),
            ':asunto'      => trim($asunto),
            ':descripcion' => trim($descripcion),
            ':tipo'        => $tipo,
            ':medio'       => $medio,
            ':solicitante' => trim($solicitante),
            ':vobo_nya'    => $vobo_nya,
            ':id'          => (int)$id
        ]);
    }


}
