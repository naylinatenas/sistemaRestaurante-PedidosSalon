<?php
require_once("../modelo/usuario.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->verificarLogin($correo, $clave);

    if ($usuario) {
        // ✅ CRÍTICO: Guardar ID del usuario en sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];  // ← ESTO ES CRÍTICO
        $_SESSION['usuario'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($usuario['rol'] == 'admin') {
            header("Location: ../vista/dashboardInicio.php");
        } else {
            header("Location: ../vista/dashboardInicio.php");
        }
        exit();
    } else {
        header("Location: ../vista/login.php?error=1");
        exit();
    }
}
?>