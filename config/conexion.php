<?php
class Conexion {
    private static $instancia = null;

    public static function conectar() {
        if (self::$instancia === null) {
            try {
                // Reemplaza 'root' y '' por tu usuario y contraseña de MySQL si es necesario
                self::$instancia = new PDO("mysql:host=localhost;dbname=sistema_soporte;charset=utf8", "root", "");
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$instancia;
    }
}
