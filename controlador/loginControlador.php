<?php
session_start();
// login demo: se puede extender
$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
if($usuario === 'mozo' && $contrasena === 'demo'){
    $_SESSION['user'] = ['usuario'=>'mozo','rol'=>'mozo'];
    header('Location: /public/index.php'); exit;
}
header('Location: /vista/login.php?error=1'); exit;
