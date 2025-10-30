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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 0 5px;
        }
        .btn-print {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
        }
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        }
        .btn-close {
            background: #6c757d;
            color: white;
        }
        .btn-close:hover {
            background: #5a6268;
        }
        .ticket {
            background: white;
            border: 2px solid #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #ff8c42;
            font-size: 2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .header p {
            color: #555;
            margin: 5px 0;
        }
        .comprobante-title {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .info p {
            margin: 8px 0;
            font-size: 15px;
            color: #333;
        }
        .info strong {
            color: #ff8c42;
            display: inline-block;
            min-width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        table thead {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        table th:nth-child(2),
        table th:nth-child(3),
        table th:nth-child(4) {
            text-align: right;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
        }
        table td:nth-child(2),
        table td:nth-child(3),
        table td:nth-child(4) {
            text-align: right;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table tbody tr:hover {
            background-color: #fff8f0;
        }
        .total {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            margin-top: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(255, 152, 0, 0.3);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #333;
        }
        .footer p {
            margin: 8px 0;
            color: #555;
        }
        .footer .agradecimiento {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ff8c42;
            margin-bottom: 10px;
        }
        .footer .emoji {
            font-size: 1.5rem;
        }
        .footer .grupo {
            font-size: 0.9rem;
            color: #999;
            margin-top: 15px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .ticket {
                box-shadow: none;
                border: 2px solid #333;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Imprimir Pedido
            </button>
            <button onclick="window.close()" class="btn btn-close">
                ✕ Cerrar
            </button>
        </div>

        <div class="ticket">
            <div class="header">
                <h1>🍽️ RESTAURANTE GRUPO 7</h1>
                <p><strong>RUC:</strong> 20123456789</p>
                <p><strong>Dirección:</strong> Av. Principal 123 - Trujillo, La Libertad</p>
                <p><strong>Teléfono:</strong> (044) 234-5678</p>
            </div>

            <div class="comprobante-title">
                COMPROBANTE DE PEDIDO
            </div>

            <div class="info">
                <p><strong>ID Pedido:</strong> <?= htmlspecialchars($pedido['id_pedido']) ?></p>
                <p><strong>Mesa Nº:</strong> <?= htmlspecialchars($pedido['numero_mesa']) ?></p>
                <p><strong>Mozo:</strong> <?= htmlspecialchars($pedido['mozo_nombre']) ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_inicio'])) ?></p>
                <?php if ($pedido['hora_cierre']): ?>
                    <p><strong>Hora Cierre:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_cierre'])) ?></p>
                <?php endif; ?>
                <p><strong>Estado:</strong> 
                    <span style="background: <?= $pedido['estado_pedido'] === 'abierto' ? '#2e7d32' : '#757575' ?>; color: white; padding: 4px 10px; border-radius: 5px; font-size: 13px;">
                        <?= ucfirst($pedido['estado_pedido']) ?>
                    </span>
                </p>
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
                    <?php if (empty($detalles)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999; padding: 20px;">
                                No hay productos en este pedido
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($detalle['plato_nombre']) ?></strong></td>
                                <td><?= $detalle['cantidad'] ?></td>
                                <td>S/ <?= number_format($detalle['precio_unit'] ?? 0, 2) ?></td>
                                <td><strong>S/ <?= number_format($detalle['subtotal'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="total">
                TOTAL: S/ <?= number_format($pedido['total'], 2) ?>
            </div>

            <div class="footer">
                <p class="agradecimiento">¡Gracias por su preferencia!</p>
                <p class="emoji">😊</p>
                <p>Vuelva pronto</p>
                <p class="grupo">Grupo 7 - Sistema de Gestión de Pedidos</p>
                <p class="grupo">📧 contacto@restaurantegrupo7.com</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>