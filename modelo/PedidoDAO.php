<?php
// Conexion.php - usar PDO
class Conexion {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = 'localhost';
        $db   = 'bd_restaurante';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $opt);
        } catch (PDOException $e) {
            exit('DB Connection failed: '.$e->getMessage());
        }
    }

    public static function getConexion() {
        if (self::$instance === null) {
            self::$instance = new Conexion();
        }
        return self::$instance->pdo;
    }
}
