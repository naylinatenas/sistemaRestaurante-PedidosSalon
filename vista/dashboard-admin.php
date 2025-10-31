<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../modelo/PedidoDAO.php';
require_once __DIR__ . '/../conexion/Conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit();
} 

// Crear DAO
$pedidoDAO = new PedidoDAO();
$pdo = Conexion::conectar();

// Datos del dashboard
$mesa_ocupadas = $pedidoDAO->mesasOcupadasCount();
$ingreso_total = $pedidoDAO->ingresoHoy();
$plato_popular = $pedidoDAO->platoMasPedidoHoy();

$stmt = $pdo->query("SELECT COUNT(*) FROM pedido WHERE DATE(hora_inicio)=CURDATE()");
$pedidos_hoy = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM pedido WHERE estado_pedido='abierto'");
$pedidos_activos = (int)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT AVG(total) FROM pedido WHERE estado_pedido='cerrado' AND DATE(hora_cierre)=CURDATE()");
$ticket_promedio = (float)$stmt->fetchColumn();
$stmt = $pdo->query("SELECT COUNT(*) FROM mesa");
$total_mesas = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Restaurante</title>

    <!-- Bootstrap / Icons / Fonts / Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../css/dashboard_style.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-utensils main-icon"></i>
        <h3>Sistema Restaurante</h3>
        <div class="user-info">
            <i class="fa-solid fa-user-shield"></i>
            <span><?= htmlspecialchars($_SESSION['usuario']); ?></span>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="active">
            <i class="fa-solid fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="gestion_platos.php">
            <i class="fa-solid fa-receipt"></i>
            <span>Gestión de Platos</span>
        </a>
    </div>

    <form action="../controlador/logout.php" method="post">
        <button type="submit" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </button>
    </form>
</div>

<!-- Main -->
<div class="main-content">
    <div class="section-header">
        <h2><i class="fa-solid fa-chart-pie"></i> Dashboard General</h2>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card-stat orange">
                <div class="stat-content">
                    <h5>Mesas Ocupadas</h5>
                    <h2><?= $mesa_ocupadas; ?></h2>
                    <small class="text-muted">de <?= $total_mesas; ?> mesas</small>
                </div>
                <i class="fa-solid fa-chair stat-icon"></i>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-stat yellow">
                <div class="stat-content">
                    <h5>Ingreso del Día</h5>
                    <h2>S/ <?= number_format($ingreso_total, 2); ?></h2>
                </div>
                <i class="fa-solid fa-money-bill-wave stat-icon"></i>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-stat blue">
                <div class="stat-content">
                    <h5>Pedidos del Día</h5>
                    <h2><?= $pedidos_hoy; ?></h2>
                    <small class="text-muted"><?= $pedidos_activos; ?> activos</small>
                </div>
                <i class="fa-solid fa-receipt stat-icon"></i>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-stat purple">
                <div class="stat-content">
                    <h5>Ticket Promedio</h5>
                    <h2>S/ <?= number_format($ticket_promedio, 2); ?></h2>
                </div>
                <i class="fa-solid fa-calculator stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="card-stat green mt-4">
        <div class="stat-content">
            <h5><i class="fa-solid fa-trophy me-2"></i>Plato Más Pedido del Día</h5>
            <h2><?= htmlspecialchars($plato_popular); ?></h2>
        </div>
        <i class="fa-solid fa-crown stat-icon"></i>
    </div>
</div>

</body>
</html>
