<?php
require_once '../modelo/PedidoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ../login.php");
    exit();
}

$pedidos = PedidoDAO::obtenerTodosLosPedidosDB();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #fffaf5;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .content-wrapper {
            padding: 2rem;
            margin-left: 1px; /* ← Ajusta este valor según el ancho de tu sidebar */
            transition: margin-left 0.3s ease;
            width: 100%;
        }

        .header {
            background: linear-gradient(135deg, #ff8c00, #ffb347);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h2 {
            font-weight: 700;
            margin: 0;
        }

        .pedido-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(255, 136, 0, 0.15);
        }

        .table thead {
            background-color: #fff3e0;
        }

        .btn-pink {
            background-color: #ff758c;
            color: white;
        }
        .btn-pink:hover {
            background-color: #e45d74;
            color: white;
        }

        .bg-orange {
            background-color: #ff8c00 !important;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
        }

        @media (max-width: 992px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="header">
            <h2><i class="bi bi-clipboard-data"></i> Historial de Pedidos</h2>
        </div>

        <div class="pedido-box">
            <?php if (empty($pedidos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay pedidos registrados aún.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Mesa</th>
                                <th>Mozo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Detalles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pedido['id_pedido']) ?></strong></td>
                                    <td><strong>Mesa <?= htmlspecialchars($pedido['numero_mesa']) ?></strong></td>
                                    <td><?= htmlspecialchars($pedido['mozo_nombre']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($pedido['hora_inicio'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $pedido['estado_pedido'] === 'abierto' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($pedido['estado_pedido']) ?>
                                        </span>
                                    </td>
                                    <td><strong>S/ <?= number_format($pedido['total'], 2) ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detallesModal<?= $pedido['id_pedido'] ?>">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="pedido.php?pedido_id=<?= urlencode($pedido['id_pedido']) ?>" class="btn btn-sm btn-pink" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success" onclick="imprimirPedido('<?= htmlspecialchars($pedido['id_pedido']) ?>')" title="Imprimir">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('<?= htmlspecialchars($pedido['id_pedido']) ?>')" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal de Detalles -->
                                <div class="modal fade" id="detallesModal<?= $pedido['id_pedido'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-orange text-white">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-receipt"></i> Detalles del Pedido - Mesa <?= htmlspecialchars($pedido['numero_mesa']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>ID Pedido:</strong> <?= htmlspecialchars($pedido['id_pedido']) ?><br>
                                                    <strong>Mozo:</strong> <?= htmlspecialchars($pedido['mozo_nombre']) ?><br>
                                                    <strong>Fecha Inicio:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_inicio'])) ?><br>
                                                    <?php if ($pedido['hora_cierre']): ?>
                                                        <strong>Fecha Cierre:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_cierre'])) ?><br>
                                                    <?php endif; ?>
                                                    <strong>Estado:</strong>
                                                    <span class="badge bg-<?= $pedido['estado_pedido'] === 'abierto' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($pedido['estado_pedido']) ?>
                                                    </span>
                                                </div>
                                                <?php $detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido['id_pedido']); ?>
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Plato</th>
                                                            <th>Precio Unit.</th>
                                                            <th>Cantidad</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($detalles as $detalle): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($detalle['plato_nombre']) ?></td>
                                                                <td>S/ <?= number_format($detalle['precio'], 2) ?></td>
                                                                <td><?= $detalle['cantidad'] ?></td>
                                                                <td>S/ <?= number_format($detalle['subtotal'], 2) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <th colspan="3" class="text-end">Total:</th>
                                                            <th>S/ <?= number_format($pedido['total'], 2) ?></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="eliminarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este pedido? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="../controlador/historialControlador.php" id="formEliminar">
                        <input type="hidden" name="action" value="eliminar_pedido">
                        <input type="hidden" name="pedido_id" id="pedido_id_eliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(pedidoId) {
            document.getElementById('pedido_id_eliminar').value = pedidoId;
            var modal = new bootstrap.Modal(document.getElementById('eliminarModal'));
            modal.show();
        }

        function imprimirPedido(pedidoId) {
            window.open('imprimir_pedido.php?pedido_id=' + pedidoId, '_blank', 'width=800,height=600');
        }
    </script>
</body>
</html>
