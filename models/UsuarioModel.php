<?php
require_once 'config/conexion.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // Busca un usuario por su credencial de acceso para el Login
    public function obtenerPorUsuario($usuario) {
        $query = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':usuario' => trim($usuario)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [CREATE] Guardar usuario con contraseña encriptada de forma segura
    public function guardar($usuario, $nombre, $tipo_usuario, $password) {
        // Encriptación nativa de PHP (PASSWORD_BCRYPT genera un string seguro de 60 caracteres)
        $password_encriptado = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO usuarios (usuario, nombre, tipo_usuario, password) VALUES (:usuario, :nombre, :tipo_usuario, :password)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':usuario' => trim($usuario),
            ':nombre' => trim($nombre),
            ':tipo_usuario' => $tipo_usuario,
            ':password' => $password_encriptado
        ]);
    }

    // [READ] Obtener lista general para el panel de administración
    /*public function obtenerTodos() {
        $query = "SELECT id, usuario, nombre, tipo_usuario FROM usuarios ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/
     public function obtenerTodos() {
        // Filtramos para traer únicamente roles 'intermedio' y 'basico'
        $query = "SELECT id, usuario, nombre, tipo_usuario 
                  FROM usuarios 
                  WHERE tipo_usuario != 'administrador' 
                  ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosAdmin() {
        $query = "SELECT id, usuario, nombre, tipo_usuario FROM usuarios ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, usuario, nombre, tipo_usuario FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // [UPDATE] Actualizar datos (Modificación simplificada sin cambiar password)
    public function actualizar($id, $usuario, $nombre, $tipo_usuario) {
        $query = "UPDATE usuarios SET usuario = :usuario, nombre = :nombre, tipo_usuario = :tipo_usuario WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':usuario' => trim($usuario),
            ':nombre' => trim($nombre),
            ':tipo_usuario' => $tipo_usuario
        ]);
    }

    public function eliminar($id) {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }


    public function actualizarPassword($id, $nueva_password) {
        // Generamos el hash seguro de 60 caracteres (BCRYPT)
        $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);

        $query = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':password' => $password_hash,
            ':id'       => (int)$id
        ]);
    }

        // Muestra la pantalla del perfil del usuario logueado con sus datos vigentes
    public function miPerfil() {
        $usuario = $this->modeloUser->obtenerPorId($_SESSION['usuario_id']);
        require_once 'views/perfil_view.php';
    }

    // NUEVO MÉTODO: Validar y procesar el cambio de Nombre y Foto de Perfil
    public function actualizarMisDatos() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_sesion = (int)$_SESSION['usuario_id'];
            $nuevo_nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $ruta_destino_final = null;

            // Procesamos la subida de la foto de perfil si el usuario seleccionó un archivo
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['foto_perfil']['tmp_name'];
                $file_name = $_FILES['foto_perfil']['name'];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Extensiones permitidas por seguridad corporativa
                $extensiones_permitidas = ['jpg', 'jpeg', 'png'];

                if (in_array($file_ext, $extensiones_permitidas)) {
                    // Renombramos el archivo con el ID único para no empalmar imágenes
                    $nuevo_nombre_archivo = "user_" . $id_sesion . "_" . time() . "." . $file_ext;
                    $ruta_destino_final = "assets/img/perfiles/" . $nuevo_nombre_archivo;

                    // Movemos el archivo de la memoria temporal a la carpeta local
                    move_uploaded_file($file_tmp, $ruta_destino_final);
                } else {
                    $_SESSION['error_perfil'] = "❌ Tipo de archivo no permitido. Solo se aceptan JPG, JPEG y PNG.";
                    header("Location: index.php?accion=mi_perfil");
                    exit;
                }
            }

            if (!empty($nuevo_nombre)) {
                $this->modeloUser->actualizarDatosPerfil($id_sesion, $nuevo_nombre, $ruta_destino_final);
                
                // Refrescamos los datos en tiempo real dentro de las variables de sesión
                $_SESSION['usuario_nombre'] = $nuevo_nombre;
                if ($ruta_destino_final) {
                    $_SESSION['usuario_foto'] = $ruta_destino_final;
                }
                $_SESSION['exito_perfil'] = "👤 Datos de perfil actualizados correctamente.";
            }
        }
        header("Location: index.php?accion=mi_perfil");
        exit;
    }

    // Procesa el cambio de contraseña solicitado por el propio usuario de forma aislada
    public function actualizarMiPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_sesion    = (int)$_SESSION['usuario_id'];
            $nueva_pass   = $_POST['nueva_password'];
            $confirm_pass = $_POST['confirm_password'];

            if (!empty($nueva_pass) && $nueva_pass === $confirm_pass) {
                $this->modeloUser->actualizarPassword($id_sesion, $nueva_pass);
                $_SESSION['exito_perfil'] = "🔑 Contraseña actualizada con éxito.";
            } else {
                $_SESSION['error_perfil'] = "❌ Las contraseñas ingresadas no coinciden.";
            }
        }
        header("Location: index.php?accion=mi_perfil");
        exit;
    }


}
