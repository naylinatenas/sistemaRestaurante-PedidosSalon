<?php
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';
session_start();

PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

$mesa_numero = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;
$mesa = PedidoDAO::getMesa($mesa_numero);
$pedido = null;

if ($mesa && $mesa->getPedidoId()) {
    $pedido = PedidoDAO::getPedidoById($mesa->getPedidoId());
}

$menu = PedidoDAO::getTodosLosPlatos();
$categorias = [];
foreach ($menu as $plato) {
    $categorias[$plato->getCategoria()][] = $plato;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Mesa <?= $mesa_numero ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="header-box">
                <h1><i class="bi bi-clipboard-check"></i> Mesa <?= $mesa_numero ?></h1>
            </div>
            <a href="mesas.php" class="btn btn-outline-pink">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
        
        <?php if (!$pedido || $pedido->getEstado() === 'cerrado'): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay pedido activo para esta mesa.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="pedido-box">
                        <h4 class="mb-3"><i class="bi bi-cart3"></i> Pedido Actual</h4>
                        
                        <?php if (empty($pedido->getItems())): ?>
                            <p class="text-muted">No hay platos en el pedido</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Plato</th>
                                            <th>Precio</th>
                                            <th>Cant.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedido->getItems() as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['plato']->getNombre()) ?></td>
                                                <td>S/ <?= number_format($item['plato']->getPrecio(), 2) ?></td>
                                                <td><?= $item['cantidad'] ?></td>
                                                <td>S/ <?= number_format($item['plato']->getPrecio() * $item['cantidad'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="total-box">
                                <h3>Total: S/ <?= number_format($pedido->getTotal(), 2) ?></h3>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="../controlador/pedidoControlador.php" class="mt-3">
                            <input type="hidden" name="action" value="cerrar_pedido">
                            <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
                            <input type="hidden" name="mesa" value="<?= $mesa_numero ?>">
                            <button type="submit" class="btn btn-danger w-100" 
                                    <?= empty($pedido->getItems()) ? 'disabled' : '' ?>>
                                <i class="bi bi-check-circle"></i> Cerrar Pedido
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="menu-box">
                        <h4 class="mb-3"><i class="bi bi-book"></i> Menú</h4>
                        
                        <?php foreach ($categorias as $categoria => $platos): ?>
                            <div class="categoria-section mb-4">
                                <h5 class="categoria-title"><?= $categoria ?></h5>
                                
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
                                            <input type="number" name="cantidad" value="1" min="1" class="form-control form-control-sm cantidad-input">
                                            <button type="submit" class="btn btn-sm btn-pink">
                                                <i class="bi bi-plus"></i> Agregar
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>