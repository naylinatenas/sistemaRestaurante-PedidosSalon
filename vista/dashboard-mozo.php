<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mozo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Bienvenido <?php echo $_SESSION['usuario']; ?> (Mozo)</h3>
    <hr>
    <p><strong>Mesas ocupadas actualmente:</strong> ...</p>
    <p><strong>Pedidos abiertos:</strong> ...</p>

    <a href="../controlador/logout.php" class="btn btn-danger">Cerrar sesión</a>
</body>
</html>
