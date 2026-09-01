<?php
require_once 'models/UsuarioModel.php';

class UsuarioController {
    private $modelo;

    public function __construct() {
        $this->modelo = new UsuarioModel();
    }

    // Carga la lista de usuarios y el formulario (en modo creación o edición)
    public function index() {
        $usuarios = $this->modelo->obtenerTodos();
        $usuarioEditar = null;

        // Si se recibe un ID por la URL, obtenemos los datos para rellenar el formulario de edición
        if (isset($_GET['editar_id'])) {
            $usuarioEditar = $this->modelo->obtenerPorId($_GET['editar_id']);
        }
        $usuariosAdmin = $this->modelo->obtenerTodosAdmin();

        require_once 'views/usuarios_view.php';
    }

    // Procesa el guardado tanto para inserción como para actualización
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id           = $_POST['id'] ?? null;
            $usuario      = trim($_POST['usuario']);
            $nombre       = trim($_POST['nombre']);
            $tipo_usuario = $_POST['tipo_usuario'];

            if ($id) {
                // Caso: Edición de un usuario existente (No altera el password actual)
                $this->modelo->actualizar($id, $usuario, $nombre, $tipo_usuario);
            } else {
                // Caso: Registro de un usuario nuevo (Requiere contraseña)
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                $this->modelo->guardar($usuario, $nombre, $tipo_usuario, $password);
            }
        }
        // Redirección limpia al terminar la operación
        header("Location: index.php?accion=usuarios");
        exit;
    }

    // Procesa la baja lógica o física de un usuario
    public function eliminar() {
        if (isset($_GET['id'])) {
            $this->modelo->eliminar($_GET['id']);
        }
        header("Location: index.php?accion=usuarios");
        exit;
    }

    // Validar y guardar la nueva contraseña
    public function cambiarPassword() {
        // Capa de seguridad: Solo el administrador puede resetear claves
        if ($_SESSION['usuario_tipo'] !== 'administrador') {
            header("Location: index.php?accion=dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_usuario_pass'];
            $nueva_pass = $_POST['nueva_password'];

            if ($id > 0 && !empty($nueva_pass)) {
                $this->modelo->actualizarPassword($id, $nueva_pass);
            }
        }
        header("Location: index.php?accion=usuarios");
        exit;
    }
  

    // [UPDATE PRIVADO] Actualizar los datos básicos del perfil (Nombre y Foto)
    public function actualizarDatosPerfil($id, $nombre, $ruta_foto = null) {
        if ($ruta_foto) {
            $query = "UPDATE usuarios SET nombre = :nombre, foto_perfil = :ruta_foto WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':nombre'    => trim($nombre),
                ':ruta_foto' => $ruta_foto,
                ':id'        => (int)$id
            ]);
        } else {
            $query = "UPDATE usuarios SET nombre = :nombre WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':nombre' => trim($nombre),
                ':id'     => (int)$id
            ]);
        }
    }

    // [READ ONE] Buscar usuario por su ID para refrescar la sesión actual
    public function obtenerPorId($id) {
        $query = "SELECT id, usuario, nombre, tipo_usuario, foto_perfil FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}
