<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../modelo/PedidoDAO.php';
ob_start();

$pedidos = PedidoDAO::obtenerTodosLosPedidosDB();
?>

<style>
    body {
        background-color: #fff8f0;
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
    }

    .container-wrapper {
        padding: 25px;
        max-width: 1600px;
        margin: 0 auto;
    }

    .header {
        background: linear-gradient(90deg, #ff9f1c, #ff7b00);
        color: white;
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .header i {
        font-size: 28px;
    }

    .header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 1.8rem;
    }

    .content {
        background: white;
        border-radius: 15px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background-color: #ffeedb;
    }

    .table thead th {
        color: #333;
        font-weight: 600;
        padding: 15px 12px;
        vertical-align: middle;
        border-bottom: 2px solid #ffd9a3;
    }

    .table tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f5f5f5;
    }

    .table tbody tr {
        transition: background-color 0.2s;
    }

    .table tbody tr:hover {
        background-color: #fffbf5;
    }

    .btn-ver {
        background-color: #00bcd4;
        color: white;
        padding: 8px 20px;
        border-radius: 6px;
        border: none;
        transition: 0.2s;
        font-weight: 500;
    }

    .btn-ver:hover {
        background-color: #0097a7;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 188, 212, 0.3);
    }

    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: none;
        margin: 0 3px;
        transition: 0.2s;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-editar {
        background-color: #ff4081;
        color: white;
    }

    .btn-editar:hover {
        background-color: #e91e63;
    }

    .btn-imprimir {
        background-color: #4caf50;
        color: white;
    }

    .btn-imprimir:hover {
        background-color: #388e3c;
    }

    .btn-eliminar {
        background-color: #f44336;
        color: white;
    }

    .btn-eliminar:hover {
        background-color: #d32f2f;
    }

    .badge-abierto {
        background-color: #2e7d32;
        color: white;
        font-size: 13px;
        padding: 7px 14px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-cerrado {
        background-color: #757575;
        color: white;
        font-size: 13px;
        padding: 7px 14px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-block;
    }

    .no-pedidos {
        text-align: center;
        font-size: 18px;
        color: #777;
        padding: 50px 20px;
    }

    /* Estilos del Modal */
    .modal-content {
        border-radius: 15px;
        border: none;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(90deg, #ff9f1c, #ff7b00);
        color: white;
        padding: 20px 25px;
        border: none;
    }

    .modal-header h5 {
        font-weight: 600;
        margin: 0;
    }

    .modal-body {
        padding: 25px;
    }

    .info-pedido {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .info-pedido p {
        margin: 8px 0;
        font-size: 15px;
    }

    .titulo-platos {
        color: #ff7b00;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.1rem;
    }

    .table-platos {
        margin-bottom: 0;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
    }

    .table-platos thead {
        background-color: #fff3e0;
    }

    .table-platos thead th {
        color: #ff7b00;
        font-weight: 600;
        padding: 12px;
        text-transform: uppercase;
        font-size: 13px;
        border-bottom: 2px solid #ffd9a3;
    }

    .table-platos tbody td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-platos tbody tr:last-child td {
        border-bottom: none;
    }

    .total-modal {
        background: linear-gradient(90deg, #ff9f1c, #ff7b00);
        color: white;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        font-size: 1.4rem;
        font-weight: 700;
        margin-top: 20px;
        box-shadow: 0 3px 10px rgba(255, 159, 28, 0.3);
    }

    .alert {
        border-radius: 8px;
        border: none;
    }
</style>

<div class="main-content">
    <div class="header">
        <i class="bi bi-bar-chart-line-fill"></i>
        <h2>Historial de Pedidos</h2>
    </div>

    <div class="content">
        <?php if (empty($pedidos)): ?>
            <div class="no-pedidos">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                <p>No hay pedidos registrados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
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
                                    <span class="<?= $pedido['estado_pedido'] === 'abierto' ? 'badge-abierto' : 'badge-cerrado' ?>">
                                        <?= ucfirst($pedido['estado_pedido']) ?>
                                    </span>
                                </td>
                                <td><strong>S/ <?= number_format($pedido['total'], 2) ?></strong></td>
                                <td>
                                    <button class="btn btn-ver" data-bs-toggle="modal" data-bs-target="#detallesModal<?= $pedido['id_pedido'] ?>">
                                        <i class="bi bi-eye-fill me-1"></i> Ver
                                    </button>
                                </td>
                                <td>
                                    <?php if ($pedido['estado_pedido'] === 'abierto'): ?>
                                        <a href="pedido.php?mesa=<?= urlencode($pedido['numero_mesa']) ?>" class="btn-action btn-editar" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    <?php endif; ?>
                                    <button class="btn-action btn-imprimir" onclick="imprimirPedido('<?= htmlspecialchars($pedido['id_pedido']) ?>')" title="Imprimir">
                                        <i class="bi bi-printer-fill"></i>
                                    </button>
                                    <button class="btn-action btn-eliminar" onclick="confirmarEliminar('<?= htmlspecialchars($pedido['id_pedido']) ?>')" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modales de Detalles -->
<?php foreach ($pedidos as $pedido): ?>
    <div class="modal fade" id="detallesModal<?= $pedido['id_pedido'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="bi bi-receipt me-2"></i>Detalles del Pedido - Mesa <?= htmlspecialchars($pedido['numero_mesa']) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="info-pedido">
                        <p><strong>ID Pedido:</strong> <?= htmlspecialchars($pedido['id_pedido']) ?></p>
                        <p><strong>Mozo:</strong> <?= htmlspecialchars($pedido['mozo_nombre']) ?></p>
                        <p><strong>Fecha Inicio:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_inicio'])) ?></p>
                        <?php if ($pedido['hora_cierre']): ?>
                            <p><strong>Fecha Cierre:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['hora_cierre'])) ?></p>
                        <?php endif; ?>
                        <p>
                            <strong>Estado:</strong>
                            <span class="<?= $pedido['estado_pedido'] === 'abierto' ? 'badge-abierto' : 'badge-cerrado' ?>">
                                <?= ucfirst($pedido['estado_pedido']) ?>
                            </span>
                        </p>
                    </div>

                    <?php $detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido['id_pedido']); ?>

                    <?php if (empty($detalles)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>No hay platos en este pedido
                        </div>
                    <?php else: ?>
                        <h6 class="titulo-platos">
                            <i class="bi bi-basket"></i> Platos del Pedido
                        </h6>
                        <table class="table table-platos">
                            <thead>
                                <tr>
                                    <th>PLATO</th>
                                    <th class="text-center">CANTIDAD</th>
                                    <th class="text-end">PRECIO UNIT.</th>
                                    <th class="text-end">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detalle['plato_nombre']) ?></td>
                                        <td class="text-center"><strong><?= $detalle['cantidad'] ?></strong></td>
                                        <td class="text-end">S/ <?= number_format($detalle['precio_unit'] ?? 0, 2) ?></td>
                                        <td class="text-end"><strong>S/ <?= number_format($detalle['subtotal'], 2) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="total-modal">
                            TOTAL: S/ <?= number_format($pedido['total'], 2) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="eliminarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Está seguro de que desea eliminar este pedido? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="../controlador/historialControlador.php" style="display: inline;">
                    <input type="hidden" name="action" value="eliminar_pedido">
                    <input type="hidden" name="pedido_id" id="pedido_id_eliminar">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

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

<?php
$contenido = ob_get_clean();
if ($_SESSION['rol'] === 'admin') {
    include_once "dashboard-admin.php";
} else {
    include_once "dashboard-mozo.php";
}
?>