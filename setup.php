<?php
require_once 'config/conexion.php';
$db = Conexion::conectar();

// Parámetros del primer administrador de pruebas
$usuario = 'admin';
$nombre = 'Administrador General';
$tipo_usuario = 'administrador';
$password_plano = '123456'; // <--- Tu contraseña de acceso

// Encriptación correcta
$password_hash = password_hash($password_plano, PASSWORD_BCRYPT);

$query = "INSERT INTO usuarios (usuario, nombre, tipo_usuario, password) VALUES (:usuario, :nombre, :tipo_usuario, :password)";
$stmt = $db->prepare($query);
$stmt->execute([
    ':usuario' => $usuario,
    ':nombre' => $nombre,
    ':tipo_usuario' => $tipo_usuario,
    ':password' => $password_hash
]);

echo "¡Usuario Administrador creado con éxito!<br>Usuario: <b>admin</b><br>Contraseña: <b>123456</b><br>Por seguridad, borra este archivo (setup.php) ahora.";
