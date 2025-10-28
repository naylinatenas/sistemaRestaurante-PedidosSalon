<?php
session_start();
require_once '../modelo/PedidoDAO.php';

PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

$mesas = PedidoDAO::getTodasLasMesas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Mesas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container py-4">
        <div class="header-box mb-4">
            <h1><i class="bi bi-grid-3x3"></i> Gestión de Mesas</h1>
        </div>
        
        <div class="row g-4">
            <?php foreach ($mesas as $mesa): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="mesa-card mesa-<?= $mesa->getEstado() ?>">
                        <div class="mesa-number">Mesa <?= $mesa->getNumero() ?></div>
                        <div class="mesa-status">
                            <span class="badge bg-<?= 
                                $mesa->getEstado() === 'libre' ? 'success' : 
                                ($mesa->getEstado() === 'ocupada' ? 'danger' : 'warning') 
                            ?>">
                                <?= ucfirst($mesa->getEstado()) ?>
                            </span>
                        </div>
                        
                        <?php if ($mesa->estaOcupada()): ?>
                            <?php $pedido = PedidoDAO::getPedidoById($mesa->getPedidoId()); ?>
                            <div class="mesa-total">
                                S/ <?= number_format($pedido->getTotal(), 2) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mesa-actions">
                            <?php if ($mesa->estaLibre()): ?>
                                <form method="POST" action="../controlador/mesaControlador.php">
                                    <input type="hidden" name="action" value="abrir_pedido">
                                    <input type="hidden" name="mesa" value="<?= $mesa->getNumero() ?>">
                                    <button type="submit" class="btn btn-pink w-100">
                                        <i class="bi bi-plus-circle"></i> Abrir Pedido
                                    </button>
                                </form>
                            <?php elseif ($mesa->estaOcupada()): ?>
                                <a href="pedido.php?mesa=<?= $mesa->getNumero() ?>" class="btn btn-pink w-100">
                                    <i class="bi bi-clipboard-check"></i> Ver Pedido
                                </a>
                            <?php else: ?>
                                <form method="POST" action="../controlador/mesaControlador.php">
                                    <input type="hidden" name="action" value="limpiar_mesa">
                                    <input type="hidden" name="mesa" value="<?= $mesa->getNumero() ?>">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-check-circle"></i> Mesa Limpia
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>