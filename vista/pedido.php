<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../modelo/PedidoDAO.php';
ob_start();

// ✅ Obtener número de mesa del parámetro GET
$mesa_numero = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;

// Debug
error_log("pedido.php - Mesa recibida: " . $mesa_numero);

if ($mesa_numero <= 0) {
?>
    <div class="container mx-auto mt-5">
        <div class="alert alert-danger">
            <h4><i class="bi bi-exclamation-triangle"></i> Error: No se especificó la mesa</h4>
            <p>El número de mesa no es válido o no fue especificado.</p>
            <p><strong>Mesa recibida:</strong> <?= htmlspecialchars($mesa_numero) ?></p>
            <a href="mesas.php" class="btn btn-primary">Volver a Mesas</a>
        </div>
    </div>
    <?php
    $contenido = ob_get_clean();
    if ($_SESSION['rol'] === 'admin') {
        include_once "dashboard-admin.php";
    } else {
        include_once "dashboard-mozo.php";
    }
    exit();
}

// ✅ Obtener datos de la mesa desde BD
$mesa = PedidoDAO::getMesa($mesa_numero);

if (!$mesa) {
    ?>
    <div class="container mx-auto mt-5">
        <div class="alert alert-danger">
            <h4><i class="bi bi-exclamation-triangle"></i> Error: Mesa no encontrada</h4>
            <p>No existe la mesa número <?= htmlspecialchars($mesa_numero) ?> en la base de datos.</p>
            <a href="mesas.php" class="btn btn-primary">Volver a Mesas</a>
        </div>
    </div>
<?php
    $contenido = ob_get_clean();
    if ($_SESSION['rol'] === 'admin') {
        include_once "dashboard-admin.php";
    } else {
        include_once "dashboard-mozo.php";
    }
    exit();
}

// ✅ Obtener pedido activo
$pedido = null;
if ($mesa->getPedidoId()) {
    $pedido = PedidoDAO::getPedidoById($mesa->getPedidoId());
    error_log("Pedido encontrado: " . ($pedido ? $pedido->getId() : 'null'));
}

// ✅ Obtener menú desde BD
$menu = PedidoDAO::getTodosLosPlatos();
$categorias = [];
foreach ($menu as $plato) {
    $categorias[$plato->getCategoria()][] = $plato;
}
?>

<style>
    body {
        background-color: #fffaf6;
        font-family: 'Poppins', sans-serif;
    }

    .header-box {
        background: linear-gradient(90deg, #ff8c00, #ff7b30);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        margin-bottom: 20px;
    }

    .header-box h1 {
        font-size: 1.8rem;
        margin: 0;
        font-weight: 700;
    }

    .pedido-box,
    .menu-box {
        background-color: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .pedido-box h4,
    .menu-box h4 {
        font-weight: 600;
        color: #333;
        border-bottom: 3px solid #ff8c00;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .table th {
        background-color: #fff3e0;
        color: #d35400;
    }

    .table td {
        vertical-align: middle;
    }

    .total-box {
        background: linear-gradient(135deg, #ff8c00, #ff7b30);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        margin-top: 15px;
    }

    .btn-pink {
        background-color: #ff7b30;
        border: none;
        color: white;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-pink:hover {
        background-color: #e65e0a;
        color: white;
        transform: scale(1.02);
    }

    .btn-danger {
        font-weight: 600;
    }

    .categoria-title {
        font-weight: 600;
        color: #ff7b30;
        border-left: 4px solid #ff7b30;
        padding-left: 10px;
        margin-bottom: 15px;
        margin-top: 20px;
    }

    .plato-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #eee;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #fff;
        transition: 0.2s;
    }

    .plato-item:hover {
        background-color: #fff5ef;
        border-color: #ff8c00;
    }

    .plato-info {
        flex: 1;
    }

    .plato-nombre {
        font-weight: 600;
        color: #333;
    }

    .plato-precio {
        color: #e65e0a;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .cantidad-input {
        width: 70px;
        text-align: center;
    }

    .plato-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .no-items-message {
        text-align: center;
        padding: 30px;
        color: #999;
    }

    .no-items-message i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 10px;
    }
</style>

<div class="main-content py-4">
    <div class="header-box">
        <h1><i class="bi bi-clipboard-check"></i> Pedido - Mesa <?= $mesa_numero ?></h1>
        <small>Estado: <span class="badge bg-light text-dark"><?= ucfirst($mesa->getEstado()) ?></span></small>
    </div>

    <?php if (!$pedido || $pedido->getEstado() === 'cerrado'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-info-circle"></i> <strong>No hay pedido activo</strong> para esta mesa.
            <a href="mesas.php" class="alert-link">Volver a mesas</a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- ========== PEDIDO ACTUAL ========== -->
            <div class="col-lg-5 mb-4">
                <div class="pedido-box">
                    <h4><i class="bi bi-cart3"></i> Pedido Actual</h4>
                    <p class="text-muted small">ID: <?= htmlspecialchars($pedido->getId()) ?></p>

                    <?php if (empty($pedido->getItems())): ?>
                        <div class="no-items-message">
                            <i class="bi bi-basket"></i>
                            <p>No hay platos en el pedido</p>
                            <small class="text-muted">Agrega platos desde el menú</small>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Plato</th>
                                        <th>Precio</th>
                                        <th>Cant.</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedido->getItems() as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['plato']->getNombre()) ?></td>
                                            <td>S/ <?= number_format($item['plato']->getPrecio(), 2) ?></td>
                                            <td class="text-center"><strong><?= $item['cantidad'] ?></strong></td>
                                            <td><strong>S/ <?= number_format($item['plato']->getPrecio() * $item['cantidad'], 2) ?></strong></td>
                                            <td>
                                                <form method="POST" action="../controlador/pedidoControlador.php" onsubmit="return confirm('¿Deseas eliminar este producto del pedido?');">
                                                    <input type="hidden" name="action" value="eliminar_plato">
                                                    <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
                                                    <input type="hidden" name="plato_id" value="<?= $item['plato']->getId() ?>">
                                                    <input type="hidden" name="mesa" value="<?= $mesa_numero ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="total-box">
                            TOTAL: S/ <?= number_format($pedido->getTotal(), 2) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Botones de acción -->
                    <form method="POST" action="../controlador/pedidoControlador.php" class="mt-3">
                        <input type="hidden" name="action" value="cerrar_pedido">
                        <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
                        <input type="hidden" name="mesa" value="<?= $mesa_numero ?>">
                        <button type="submit" class="btn btn-danger w-100" <?= empty($pedido->getItems()) ? 'disabled' : '' ?>>
                            <i class="bi bi-check-circle"></i> Cerrar Pedido
                        </button>
                    </form>

                    <a href="mesas.php" class="btn btn-secondary w-100 mt-2">
                        <i class="bi bi-arrow-left"></i> Volver a Mesas
                    </a>
                    <a href="historialPedidos.php" class="btn btn-secondary w-100 mt-2">
                        <i class="bi bi-clock-history"></i> Ver Historial de Pedidos
                    </a>
                </div>
            </div>

            <!-- ========== MENÚ ========== -->
            <div class="col-lg-7">
                <div class="menu-box">
                    <h4><i class="bi bi-book"></i> Menú Disponible</h4>

                    <?php if (empty($categorias)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> No hay platos disponibles en el menú
                        </div>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria => $platos): ?>
                            <h5 class="categoria-title"><?= htmlspecialchars($categoria) ?></h5>
                            <?php foreach ($platos as $plato): ?>
                                <div class="plato-item">
                                    <div class="plato-info">
                                        <div class="plato-nombre"><?= htmlspecialchars($plato->getNombre()) ?></div>
                                        <div class="plato-precio">S/ <?= number_format($plato->getPrecio(), 2) ?></div>
                                    </div>
                                    <form method="POST" action="../controlador/pedidoControlador.php" class="plato-form">
                                        <input type="hidden" name="action" value="agregar_plato">
                                        <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
                                        <input type="hidden" name="plato_id" value="<?= $plato->getId() ?>">
                                        <input type="hidden" name="mesa" value="<?= $mesa_numero ?>">
                                        <input type="number" name="cantidad" value="1" min="1" max="99" class="form-control form-control-sm cantidad-input">
                                        <button type="submit" class="btn btn-sm btn-pink">
                                            <i class="bi bi-plus-lg"></i> Agregar
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <?php unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-refresh cada 30 segundos
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>

<?php
$contenido = ob_get_clean();
if ($_SESSION['rol'] === 'admin') {
    include_once "dashboard-admin.php";
} else {
    include_once "dashboard-mozo.php";
}
?>