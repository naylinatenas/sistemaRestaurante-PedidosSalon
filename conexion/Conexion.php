<?php
class Conexion {
    public static function conectar() {
        try {
            $con = new PDO("mysql:host=localhost;port=3307;dbname=restauranteGrupo7", "root", "");
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $con;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
} 
?>