<?php
require_once 'models/InventarioModel.php';

class InventarioController {
    private $modeloInv;

    public function __construct() {
        // Inicializamos el modelo para toda la clase
        $this->modeloInv = new InventarioModel();
    }

    // Listado general del módulo con captura de filtros dinámicos
      public function index() {
        // AJUSTADO: El límite por defecto ahora es 10 si viene vacío
        $registros_por_pagina = isset($_GET['f_limite']) && $_GET['f_limite'] !== '' ? (int)$_GET['f_limite'] : 10;
        $pagina_actual        = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
        $inicio_limit         = ($pagina_actual - 1) * $registros_por_pagina;

        $filtros = [
            'nombre'     => $_GET['f_nombre'] ?? '',
            'bajo_stock' => $_GET['f_bajo_stock'] ?? ''
        ];

        // 1. Universo TOTAL de registros filtrados
        $todosLosProductosFiltrados = $this->modeloInv->obtenerFiltrados($filtros);
        $total_registros_inventario = count($todosLosProductosFiltrados);
        $total_paginas_inventario   = ($total_registros_inventario > 0) ? ceil($total_registros_inventario / $registros_por_pagina) : 1;

        // 2. Recorte elástico del array por servidor
        $productos = array_slice($todosLosProductosFiltrados, $inicio_limit, $registros_por_pagina);
        
        $itemEditar = null;
        if (isset($_GET['editar_id'])) {
            $itemEditar = $this->modeloInv->obtenerPorId((int)$_GET['editar_id']);
        }

        require_once 'views/inventario_view.php';
    }


    // Registrar o actualizar un insumo de forma manual en la Base de Datos
    public function guardar() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=inventario");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_lista    = $_POST['producto_existente_id'] ?? 'NUEVO';
            $id_url      = $_POST['id'] ?? null;
            
            // FORZAMOS EL CONTEXTO EN MAYÚSCULAS MULTIBYTE CON SOPORTE PARA Ñ Y ACENTOS
            $nombre      = mb_strtoupper(trim($_POST['nombre']), 'UTF-8');
            $categoria   = $_POST['categoria'] ?? 'Otros'; 
            $stock_input = (int)$_POST['stock'];

            // CASO A: Se seleccionó un producto ya existente desde el menú de la lista
            if ($id_lista !== 'NUEVO') {
                $this->modeloInv->importarFilaCSV($nombre, $categoria, $stock_input);
                $_SESSION['exito_inventario'] = "📦 Stock incrementado con éxito para el producto seleccionado.";
            } 
            // CASO B: Registro nuevo o edición directa de URL
            else {
                if ($id_url) {
                    $this->modeloInv->actualizar($id_url, $nombre, $categoria, $stock_input);
                    $_SESSION['exito_inventario'] = "✏️ Ficha de producto actualizada correctamente.";
                } else {
                    $this->modeloInv->guardar($nombre, $categoria, $stock_input);
                    $_SESSION['exito_inventario'] = "📦 Producto nuevo registrado e integrado a la lista con éxito.";
                }
            }
        }
        header("Location: index.php?accion=inventario");
        exit;
    }


    // Registrar salidas rápidas de consumibles o equipos del stock
    public function registrarConsumo() {
        // Bloqueo de seguridad preventivo
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?accion=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CAPTURA EXACTA DE LAS VARIABLES DEL FORMULARIO MODAL
            $id_producto = isset($_POST['id_descontar']) ? (int)$_POST['id_descontar'] : 0;
            $cantidad    = isset($_POST['cantidad_descontar']) ? (int)$_POST['cantidad_descontar'] : 0;

            if ($id_producto > 0 && $cantidad > 0) {
                // Ejecutamos la reducción transaccional en el modelo
                $resultado = $this->modeloInv->descontarStock($id_producto, $cantidad);
                
                if ($resultado) {
                    $_SESSION['exito_inventario'] = "➖ Se registraron las salidas del almacén exitosamente.";
                } else {
                    $_SESSION['error_inventario'] = "❌ Error: No se pudo descontar el stock del producto.";
                }
            } else {
                $_SESSION['error_inventario'] = "⚠️ Datos inválidos para procesar la salida de almacén.";
            }
        }
        header("Location: index.php?accion=inventario");
        exit;
    }


    // Eliminación física (Exclusivo Administradores)
    public function eliminar() {
        if ($_SESSION['usuario_tipo'] !== 'administrador') {
            header("Location: index.php?accion=inventario");
            exit;
        }

        if (isset($_GET['id']) && $_GET['id'] > 0) {
            $this->modeloInv->eliminar((int)$_GET['id']);
        }
        header("Location: index.php?accion=inventario");
        exit;
    }

    // Procesar tramas binarias de un archivo CSV para inyección masiva
    public function importarCSV() {
        if ($_SESSION['usuario_tipo'] === 'basico') {
            header("Location: index.php?accion=inventario");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
            $file = $_FILES['archivo_csv'];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if ($ext === 'csv') {
                    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                        
                        // Brincamos de manera explícita la primera línea de encabezados
                        fgetcsv($handle, 1000, ",");

                        $filas_procesadas = 0;
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if (count($data) >= 3) {
                                $nombre      = $data[0];
                                $descripcion = $data[1] ?? '';
                                $categoria   = trim($data[2]);
                                $stock       = $data[3] ?? 0;

                                if (!empty($nombre)) {
                                    $this->modeloInv->importarFilaCSV($nombre, $descripcion, $categoria, $stock);
                                    $filas_procesadas++;
                                }
                            }
                        }
                        fclose($handle);
                        $_SESSION['exito_inventario'] = "📦 ¡Carga masiva completada! Se integraron y acumularon {$filas_procesadas} productos.";
                    } else {
                        $_SESSION['error_inventario'] = "❌ Error al abrir el flujo del archivo temporal.";
                    }
                } else {
                    $_SESSION['error_inventario'] = "❌ Formato denegado. El archivo debe contar con la extensión .csv";
                }
            } else {
                $_SESSION['error_inventario'] = "❌ Por favor, selecciona un archivo CSV antes de procesar.";
            }
        }
        header("Location: index.php?accion=inventario");
        exit;
    }

    // Exportación dinámica acoplada a la segmentación que el usuario tenga activa
    public function exportarExcel() {
        $filtros = [
            'nombre'     => $_GET['f_nombre'] ?? '',
            'bajo_stock' => $_GET['f_bajo_stock'] ?? ''
        ];

        $data = $this->modeloInv->obtenerFiltrados($filtros);

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Inventario_Tecnologico_2026.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'>";
        echo "<tr style='background-color: #0d6efd; color: #FFF; font-weight: bold;'>
                <th>ID</th><th>Nombre Producto</th><th>Descripcion Tecnica</th><th>Categoria</th><th>Existencias</th><th>Ultima Actualizacion</th>
              </tr>";

        foreach ($data as $row) {
            $alerta = ($row['stock'] < 5) ? ' (STOCK CRÍTICO)' : '';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['nombre']) . "</td>
                    <td>" . htmlspecialchars($row['descripcion'] ?? 'Sin datos') . "</td>
                    <td>{$row['categoria']}</td>
                    <td style='font-weight: bold;" . ($row['stock'] < 5 ? "color: red;" : "") . "'>{$row['stock']}{$alerta}</td>
                    <td>{$row['fecha_actualizacion']}</td>
                  </tr>";
        }
        echo "</table>";
        exit;
    }
}
