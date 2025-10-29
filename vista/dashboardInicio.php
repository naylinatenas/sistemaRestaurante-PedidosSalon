<?php
require_once '../modelo/PedidoDAO.php';
require_once '../modelo/Mesa.php';
session_start();

$mesas = PedidoDAO::getTodasLasMesas();
$pedidos = PedidoDAO::getTodosLosPedidos();

$mesasOcupadas = 0;
$ingresoTotal = 0;
$conteoPlatos = [];

foreach ($pedidos as $pedido) {
    if ($pedido['estado_pedido'] === 'abierto' || $pedido['estado_pedido'] === 'cerrado') {
        $ingresoTotal += $pedido['total'] ?? 0;
        $detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido['id_pedido']);
        foreach ($detalles as $detalle) {
            $nombrePlato = $detalle['plato_nombre'];
            $conteoPlatos[$nombrePlato] = ($conteoPlatos[$nombrePlato] ?? 0) + $detalle['cantidad'];
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
    <title>Inicio - Dashboard Mozo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --orange-primary: #ff8c00;
            --orange-dark: #e67e00;
            --orange-light: #fff7ec;
            --gradient-orange: linear-gradient(135deg, #ff8c00, #ffb347);
            --bg-light: #fffaf5;
        }

        body {
            background: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .header {
            background: var(--gradient-orange);
            color: white;
            padding: 2rem 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(255, 140, 0, 0.3);
        }

        .header h1 {
            font-weight: 700;
            font-size: 2rem;
        }

        .header p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .card {
            border: none;
            border-radius: 18px;
            background: white;
            box-shadow: 0 4px 15px rgba(255, 136, 0, 0.15);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 25px rgba(255, 140, 0, 0.25);
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gradient-orange);
        }

        .card i {
            font-size: 2.5rem;
            color: var(--orange-dark);
            background: var(--orange-light);
            padding: 1rem;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .card h5 {
            color: var(--orange-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card p {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .footer {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-top: 3rem;
        }

        .btn-orange {
            background: var(--gradient-orange);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .btn-orange:hover {
            background: linear-gradient(135deg, #e67e00, #ff9900);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="header mb-5 text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-speedometer2 me-2"></i> Panel de Control</h1>
                <p>Resumen del desempeño actual del restaurante</p>
            </div>
            <div>
                <span class="badge bg-light text-dark p-3 fs-6">
                    <i class="bi bi-calendar3"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-people-fill"></i>
                <h5>Mesas Ocupadas</h5>
                <p><?= $mesasOcupadas ?></p>
                <button class="btn btn-orange mt-3" onclick="window.parent.location='mesas.php'">
                    <i class="bi bi-eye"></i> Ver Mesas
                </button>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-cash-coin"></i>
                <h5>Ingreso Total del Día</h5>
                <p>S/ <?= number_format($ingresoTotal, 2) ?></p>
                <button class="btn btn-orange mt-3" onclick="window.parent.location='historialPedidos.php'">
                    <i class="bi bi-receipt"></i> Ver Pedidos
                </button>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center p-4">
                <i class="bi bi-trophy-fill"></i>
                <h5>Plato Más Pedido</h5>
                <p><?= htmlspecialchars($platoMasPedido) ?></p>
            </div>
        </div>
    </div>

    <div class="footer mt-5">
        <p>🍽️ Sistema de Gestión de Restaurante — <strong>Modo Mozo</strong></p>
    </div>
</div>
</body>
</html>

