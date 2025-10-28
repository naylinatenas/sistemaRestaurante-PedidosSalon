<?php
// 🔹 Cargar todas las clases ANTES del session_start()
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ../login.php");
    exit();
}

PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

// 🔸 Obtener datos actuales
$mesas = PedidoDAO::getTodasLasMesas();
$pedidos = $_SESSION['pedidos'] ?? [];

$mesasOcupadas = 0;
$ingresoTotal = 0;
$conteoPlatos = [];

foreach ($pedidos as $pedido) {
    if ($pedido instanceof Pedido) {
        if ($pedido->getEstado() === 'abierto' || $pedido->getEstado() === 'cerrado') {
            foreach ($pedido->getDetalles() as $detalle) {
                $plato = $detalle['plato'];
                $cantidad = $detalle['cantidad'];
                $precio = $plato->getPrecio();

                $ingresoTotal += $precio * $cantidad;

                $nombrePlato = $plato->getNombre();
                if (!isset($conteoPlatos[$nombrePlato])) {
                    $conteoPlatos[$nombrePlato] = 0;
                }
                $conteoPlatos[$nombrePlato] += $cantidad;
            }
        }
    }
}

foreach ($mesas as $mesa) {
    if ($mesa->getEstado() === 'ocupada') {
        $mesasOcupadas++;
    }
}

$platoMasPedido = !empty($conteoPlatos)
    ? array_search(max($conteoPlatos), $conteoPlatos)
    : 'Ninguno aún';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mozo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --orange-primary: #ff8c00;
            --orange-dark: #e67e00;
            --orange-light: #fff4e6;
            --bg-light: #fffaf5;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, var(--orange-primary), var(--orange-dark));
            color: white;
            padding: 2rem 1rem;
            position: fixed;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
        }
        .sidebar h3 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }
        .nav-link {
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
            text-decoration: none;
        }
        .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
        }
        .logout {
            background: #fff;
            color: var(--orange-dark);
            text-align: center;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .logout:hover {
            background: var(--orange-light);
        }
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .header h2 {
            color: var(--orange-dark);
            font-weight: 700;
        }
        /* Cards */
        .card-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 4px 12px rgba(255, 122, 0, 0.2);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 122, 0, 0.3);
        }
        .card i {
            font-size: 2rem;
            color: var(--orange-primary);
        }
        .card h5 {
            color: var(--orange-dark);
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .card p {
            font-size: 1.5rem;
            font-weight: bold;
        }
        /* Buttons */
        .btn-orange {
            background: linear-gradient(90deg, var(--orange-primary), #ffb347);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .btn-orange:hover {
            background: linear-gradient(90deg, #e07b00, #ff9a26);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h3><i class="bi bi-cup-hot"></i> Restaurante</h3>
            <a href="#" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a>
            <a href="mesas.php" class="nav-link"><i class="bi bi-grid-3x3-gap"></i> Mesas</a>
            <a href="pedido.php" class="nav-link"><i class="bi bi-clipboard-check"></i> Pedidos</a>
            <a href="#" class="nav-link"><i class="bi bi-graph-up"></i> Reportes</a>
            <a href="#" class="nav-link"><i class="bi bi-person-lines-fill"></i> Perfil</a>
        </div>
        <a href="../controlador/logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div class="header">
            <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?> 👋</h2>
            <span class="text-muted"><?php echo date('d/m/Y'); ?></span>
        </div>

        <div class="card-box">
            <div class="card">
                <i class="bi bi-people-fill"></i>
                <h5>Mesas Ocupadas</h5>
                <p><?php echo $mesasOcupadas; ?></p>
            </div>
            <div class="card">
                <i class="bi bi-cash-coin"></i>
                <h5>Ingreso Total del Día</h5>
                <p>S/ <?php echo number_format($ingresoTotal, 2); ?></p>
            </div>
            <div class="card">
                <i class="bi bi-trophy-fill"></i>
                <h5>Plato Más Pedido</h5>
                <p><?php echo htmlspecialchars($platoMasPedido); ?></p>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="mesas.php" class="btn btn-orange me-3"><i class="bi bi-table"></i> Ir a Mesas</a>
            <a href="pedido.php" class="btn btn-orange me-3"><i class="bi bi-journal-text"></i> Ver Pedidos</a>
        </div>
    </div>
</body>
</html>
