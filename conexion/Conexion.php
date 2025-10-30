<?php
class Conexion
{
    private static $host = "sql213.infinityfree.com";
    private static $user = "if0_40280307";
    private static $pass = "3uhNCcjClfuwu";
    private static $db   = "if0_40280307_sistemarestaurante";
    private static $con  = null;

    //    public static function conectar()
    //    {
    //        try {
    //            $con = new PDO("mysql:host=localhost;port=3307;dbname=restauranteGrupo7", "root", "");
    //            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //            return $con;
    //        } catch (PDOException $e) {
    //            die("Error de conexión: " . $e->getMessage());
    //        }
    //    }
    public static function conectar()
    {
        if (self::$con == null) {
            self::$con = new mysqli(self::$host, self::$user, self::$pass, self::$db);
            if (self::$con->connect_error) {
                die("❌ Error de conexión: " . self::$con->connect_error);
            }
        }
        return self::$con;
    }
}
