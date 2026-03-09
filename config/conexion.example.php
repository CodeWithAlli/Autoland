<?php
/**
 * AUTOLAND — Configuración de conexión
 * Copia este archivo como "conexion.php" y edita tus credenciales.
 */
class Conexion
{
    private $con;
    private $db       = "mysql:host=localhost;dbname=autoland_bd;charset=utf8mb4";
    private $user     = "root";       // Cambia por tu usuario de MySQL
    private $password = "";           // Cambia por tu contraseña de MySQL

    public function __construct() {}

    public function getConnection()
    {
        try {
            $this->con = new PDO($this->db, $this->user, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
        return $this->con;
    }
}
