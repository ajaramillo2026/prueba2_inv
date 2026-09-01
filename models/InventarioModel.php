<?php
require_once 'config/conexion.php';

class InventarioModel {
    private $db;

    public function __construct() {
        // Inicializamos la conexión PDO a través de la clase central de configuración
        $this->db = Conexion::conectar();
    }

    // [CREATE] Registrar un nuevo insumo de forma manual forzando MAYÚSCULAS
    public function guardar($nombre, $categoria, $stock) {
        $query = "INSERT INTO inventario (nombre, categoria, stock) 
                  VALUES (:nombre, :categoria, :stock)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre'    => mb_strtoupper(trim($nombre), 'UTF-8'),
            ':categoria' => $categoria,
            ':stock'     => max(0, (int)$stock)
        ]);
    }

    // [READ + FILTROS] Listado dinámico con buscador parcial y alerta de stock bajo
    public function obtenerFiltrados($filtros = []) {
        $sql = "SELECT * FROM inventario WHERE 1=1";
        $params = [];

        // Filtro 1: Coincidencia parcial por nombre de producto (se evalúa en mayúsculas)
        if (!empty($filtros['nombre'])) {
            $sql .= " AND nombre LIKE :nombre";
            $params[':nombre'] = '%' . mb_strtoupper(trim($filtros['nombre']), 'UTF-8') . '%';
        }

        // Filtro 2: Alerta legal de Stock Crítico corporativo (Menos de 5 unidades)
        if (isset($filtros['bajo_stock']) && $filtros['bajo_stock'] === 'si') {
            $sql .= " AND stock < 5";
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [READ ONE] Obtener la ficha única de un insumo por su ID para edición rápida
    public function obtenerPorId($id) {
        $query = "SELECT * FROM inventario WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [UPDATE] Modificar las propiedades de un insumo tecnológico forzando MAYÚSCULAS
    public function actualizar($id, $nombre, $categoria, $stock) {
        $query = "UPDATE inventario 
                  SET nombre = :nombre, categoria = :categoria, stock = :stock 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre'    => mb_strtoupper(trim($nombre), 'UTF-8'),
            ':categoria' => $categoria,
            ':stock'     => max(0, (int)$stock),
            ':id'        => (int)$id
        ]);
    }

    // [ACTION] Descontar Stock de forma física por consumo interno de soporte
    public function descontarStock($id, $cantidad_a_restar) {
        // Recuperamos las existencias actuales para evitar caídas por debajo de cero
        $actual = $this->obtenerPorId($id);
        if (!$actual) return false;

        $nuevo_stock = max(0, $actual['stock'] - (int)$cantidad_a_restar);

        $query = "UPDATE inventario SET stock = :nuevo_stock WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nuevo_stock' => $nuevo_stock,
            ':id'          => (int)$id
        ]);
    }

    // [DELETE] Eliminar permanentemente un registro (Exclusivo Administrador)
    public function eliminar($id) {
        $query = "DELETE FROM inventario WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => (int)$id]);
    }

    // [IMPORT] Procesar e Inyectar fila CSV forzando MAYÚSCULAS para acumular de forma limpia
    public function importarFilaCSV($nombre, $categoria, $stock) {
        // Forzamos conversión en mayúsculas multibyte para evitar duplicados por diferencias de caja
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        $stock  = max(0, (int)$stock);

        // Homologación de categorías contra las restricciones ENUM de MySQL
        $categorias_validas = ['Computo', 'Redes', 'Perifericos', 'Consumibles', 'Otros'];
        if (!in_array($categoria, $categorias_validas)) {
            $categoria = 'Otros'; // Fallback seguro
        }

        // Verificamos transaccionalmente si el producto ya existe por su nombre
        $queryCheck = "SELECT id, stock FROM inventario WHERE nombre = :nombre";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->execute([':nombre' => $nombre]);
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Si ya existe, se suma el stock entrante del archivo para no duplicar filas
            $nuevo_stock = $existe['stock'] + $stock;
            $queryUp = "UPDATE inventario 
                        SET stock = :nuevo_stock, categoria = :categoria 
                        WHERE id = :id";
            $stmtUp = $this->db->prepare($queryUp);
            return $stmtUp->execute([
                ':nuevo_stock' => $nuevo_stock,
                ':categoria'   => $categoria,
                ':id'          => $existe['id']
            ]);
        } else {
            // Si es un producto nuevo en el almacén, se registra desde cero
            return $this->guardar($nombre, $categoria, $stock);
        }
    }
}
