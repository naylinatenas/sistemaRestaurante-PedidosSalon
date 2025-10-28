<?php
require_once("../modelo/usuario.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->verificarLogin($correo, $clave);

    if ($usuario) {
        $_SESSION['usuario'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($usuario['rol'] == 'admin') {
            header("Location: ../vista/dashboard-admin.php");
        } else {
            header("Location: ../vista/dashboard-mozo.php");
        }
        exit();
    } else {
        header("Location: ../vista/login.php?error=1");
        exit();
    }
}
?>