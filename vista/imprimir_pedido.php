<?php
require_once '../modelo/PedidoDAO.php';
session_start();

if (!isset($_GET['pedido_id'])) {
    echo "Pedido no especificado.";
    exit;
}

$pedido_id = $_GET['pedido_id'];
$pedido = PedidoDAO::obtenerPedidoPorIdDB($pedido_id);

if (!$pedido) {
    echo "Pedido no encontrado";
    exit;
}

$detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Pedido - <?= htmlspecialchars($pedido_id) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .ticket {
            border: 2px solid #333;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #ff8c42;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background: #ff8c42;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            text-align: right;
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px dashed #333;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #ff8c42; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <div class="ticket">
        <div class="header">
            <h1>🍽️ RESTAURANTE GRUPO 7</h1>
            <p>RUC: 20123456789</p>
            <p>Dirección: Av. Principal 123 - Trujillo</p>
            <p>Teléfono: (044) 234-5678</p>
        </div>

        <div class="info">
            <p><strong>COMPROBANTE DE PEDIDO</strong></p>
            <p><strong>ID Pedido:</strong> <?= htmlspecialchars($pedido['id_pedido']) ?></p>
            <p><strong>Mesa:</strong> <?= htmlspecialchars($pedido['numero_mesa']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_inicio'])) ?></p>
            <p><strong>Estado:</strong> <?= ucfirst($pedido['estado_pedido']) ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cant.</th>
                    <th>P. Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?= htmlspecialchars($detalle['plato_nombre']) ?></td>
                        <td><?= $detalle['cantidad'] ?></td>
                        <td>S/ <?= number_format($detalle['precio'], 2) ?></td>
                        <td>S/ <?= number_format($detalle['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            TOTAL: S/ <?= number_format($pedido['total'], 2) ?>
        </div>

        <div class="footer">
            <p>¡Gracias por su preferencia!</p>
            <p>Vuelva pronto 😊</p>
            <p style="font-size: 0.8em; color: #666;">Grupo 7 - Sistema de Pedidos</p>
        </div>
    </div>
</body>
</html>
