<?php
// 🔹 Cargar clases
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';

session_start();

// 🔒 Validar sesión y rol
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ../login.php");
    exit();
}

// 🔸 Inicializar datos si es necesario
PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

// 🔸 Obtener datos desde la base de datos
$mesas = PedidoDAO::getTodasLasMesas();
$pedidos = PedidoDAO::getTodosLosPedidos(); // ahora devuelve arrays

// 🔹 Variables para los indicadores del dashboard
$mesasOcupadas = 0;
$ingresoTotal = 0;
$conteoPlatos = [];

// 🔸 Calcular estadísticas
foreach ($pedidos as $pedido) {
    if ($pedido['estado_pedido'] === 'abierto' || $pedido['estado_pedido'] === 'cerrado') {
        $ingresoTotal += $pedido['total'] ?? 0;

        $detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido['id_pedido']);

        foreach ($detalles as $detalle) {
            $nombrePlato = $detalle['plato_nombre'];
            $cantidad = $detalle['cantidad'];

            if (!isset($conteoPlatos[$nombrePlato])) {
                $conteoPlatos[$nombrePlato] = 0;
            }
            $conteoPlatos[$nombrePlato] += $cantidad;
        }
    }
}

// 🔸 Contar mesas ocupadas
foreach ($mesas as $mesa) {
    if ($mesa->getEstado() === 'ocupada') {
        $mesasOcupadas++;
    }
}

// 🔸 Plato más pedido
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
    <link rel="stylesheet" href="../css/style.css">
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
            overflow: hidden;
        }
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
        .main-content {
            flex: 1;
            margin-left: 240px;
            height: 100vh;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: var(--bg-light);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <h3><i class="bi bi-cup-hot"></i> Restaurante</h3>
            <a href="dashboardInicio.php" target="contenido" class="nav-link active">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="mesas.php" target="contenido" class="nav-link">
                <i class="bi bi-grid-3x3-gap"></i> Mesas
            </a>
            <a href="historialPedidos.php" target="contenido" class="nav-link">
                <i class="bi bi-clipboard-check"></i> Pedidos
            </a>
        </div>
        <a href="../controlador/logout.php" class="logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
    </div>

    <!-- Contenido dinámico -->
    <div class="main-content">
        <iframe name="contenido" src="dashboardInicio.php"></iframe>
    </div>
</body>
</html>
