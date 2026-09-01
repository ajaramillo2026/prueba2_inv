<?php
require_once 'models/UsuarioModel.php';

class LoginController {
    private $modeloUser;

    public function __construct() {
        $this->modeloUser = new UsuarioModel();
    }

    // Muestra el formulario de acceso
    public function mostrarLogin() {
        // Si ya hay una sesión activa, saltamos directamente al panel principal
        if (isset($_SESSION['usuario_id'])) {
            header("Location: index.php?accion=dashboard");
            exit;
        }
        require_once 'views/login_view.php';
    }

    // Procesa las credenciales enviadas por el formulario POST
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $txt_usuario  = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
            $txt_password = isset($_POST['password']) ? $_POST['password'] : '';

            // Buscamos al usuario por su identificador único
            $user_data = $this->modeloUser->obtenerPorUsuario($txt_usuario);

            if ($user_data) {
                // Verificamos si la contraseña coincide con el Hash de la base de datos
                if (password_verify($txt_password, $user_data['password'])) {
                    
                    // Almacenamos los privilegios en la sesión global
                    $_SESSION['usuario_id']     = $user_data['id'];
                    $_SESSION['usuario_login']  = $user_data['usuario'];
                    $_SESSION['usuario_nombre'] = $user_data['nombre'];
                    $_SESSION['usuario_tipo']   = $user_data['tipo_usuario'];

                    header("Location: index.php?accion=dashboard");
                    exit;
                } else {
                    $_SESSION['error_login'] = "La contraseña es incorrecta.";
                }
            } else {
                $_SESSION['error_login'] = "El usuario ingresado no existe.";
            }

            // Si falla la autenticación, regresa al formulario con el error
            header("Location: index.php?accion=login");
            exit;
        }
    }

        public function miPerfil() {
        // Recuperamos los datos frescos desde la BD usando el ID guardado en la sesión
        $usuario = $this->modeloUser->obtenerPorId($_SESSION['usuario_id']);
        require_once 'views/perfil_view.php';
    }

    // NUEVO MÉTODO: Procesa el cambio de contraseña solicitado por el propio usuario
    public function actualizarMiPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_sesion   = (int)$_SESSION['usuario_id'];
            $nueva_pass  = $_POST['nueva_password'];
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


    // Cierra la sesión borrando rastros del servidor y del navegador
    public function cerrarSesion() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, 
                $params["path"], $params["domain"], 
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: index.php?accion=login");
        exit;
    }
}
