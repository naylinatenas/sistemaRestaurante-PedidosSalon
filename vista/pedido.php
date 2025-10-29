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
    <title>Pedido - Mesa <?= $mesa_numero ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #fffaf6;
            font-family: 'Poppins', sans-serif;
        }

        .header-box {
            background-color: #ff7b30;
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .pedido-box, .menu-box {
            background-color: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .pedido-box h4, .menu-box h4 {
            font-weight: 600;
            color: #333;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .total-box {
            background-color: #ffefe6;
            border-radius: 10px;
            padding: 10px 15px;
            text-align: center;
            color: #d35400;
            font-weight: 600;
            margin-top: 10px;
        }

        .btn-pink {
            background-color: #ff7b30;
            border: none;
            color: white;
            transition: 0.3s;
        }

        .btn-pink:hover {
            background-color: #e65e0a;
            color: white;
        }

        .categoria-title {
            font-weight: 600;
            color: #ff7b30;
            border-left: 4px solid #ff7b30;
            padding-left: 10px;
            margin-bottom: 10px;
        }

        .plato-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #eee;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #fff;
            transition: 0.2s;
        }

        .plato-item:hover {
            background-color: #fff5ef;
        }

        .plato-info {
            flex: 1;
        }

        .plato-nombre {
            font-weight: 500;
            color: #333;
        }

        .plato-precio {
            color: #e65e0a;
            font-weight: 600;
        }

        .cantidad-input {
            width: 60px;
            display: inline-block;
            text-align: center;
        }

        .plato-form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Adaptación para integrarse al dashboard */
        .contenido-principal {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="contenido-principal">
        <div class="header-box mb-4">
            <h1 class="h4 mb-0"><i class="bi bi-clipboard-check"></i> Pedido - Mesa <?= $mesa_numero ?></h1>
        </div>

        <?php if (!$pedido || $pedido->getEstado() === 'cerrado'): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay pedido activo para esta mesa.
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Pedido actual -->
                <div class="col-lg-5 mb-4">
                    <div class="pedido-box">
                        <h4><i class="bi bi-cart3"></i> Pedido Actual</h4>
                        
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
                                Total: S/ <?= number_format($pedido->getTotal(), 2) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="../controlador/pedidoControlador.php" class="mt-3">
                            <input type="hidden" name="action" value="cerrar_pedido">
                            <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
                            <input type="hidden" name="mesa" value="<?= $mesa_numero ?>">
                            <button type="submit" class="btn btn-danger w-100" <?= empty($pedido->getItems()) ? 'disabled' : '' ?>>
                                <i class="bi bi-check-circle"></i> Cerrar Pedido
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Menú -->
                <div class="col-lg-7">
                    <div class="menu-box">
                        <h4><i class="bi bi-book"></i> Menú</h4>
                        <?php foreach ($categorias as $categoria => $platos): ?>
                            <div class="categoria-section mb-3">
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
                                            <input type="number" name="cantidad" value="1" min="1" class="form-control form-control-sm cantidad-input">
                                            <button type="submit" class="btn btn-sm btn-pink">
                                                <i class="bi bi-plus"></i>
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
</body>
</html>
