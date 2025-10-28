<?php
require_once("../conexion/Conexion.php");

class Usuario {
    private $con;

    public function __construct() {
        $this->con = Conexion::conectar();
    }

    public function verificarLogin($correo, $clave) {
        $sql = "SELECT * FROM usuario WHERE correo = :correo AND estado = 'activo'";
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['clave'] === $clave) {
            return $usuario;
        } else {
            return false;
        }
    }
}
?>
