<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>Bienvenido <?php echo $_SESSION['usuario']; ?> (Administrador)</h3>
    <hr>
    <p><strong>Mesas ocupadas:</strong> ...</p>
    <p><strong>Ingreso total del día:</strong> ...</p>
    <p><strong>Plato más pedido:</strong> ...</p>

    <a href="../controlador/logout.php" class="btn btn-danger">Cerrar sesión</a>
</body>
</html>
