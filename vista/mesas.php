<?php
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ../login.php");
    exit();
}

PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();
$mesas = PedidoDAO::getTodasLasMesas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mesas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --orange-primary: #ff8c00;
            --orange-dark: #e67e00;
            --orange-light: #fff4e6;
            --bg-body: #fffaf5;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 1rem;
        }

        /* Encabezado */
        .header-box {
            background: linear-gradient(90deg, var(--orange-primary), var(--orange-dark));
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(255, 122, 0, 0.3);
        }

        .header-box h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }

        /* Tarjetas de mesas */
        .mesa-card {
            background: #fff;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid transparent;
            box-shadow: 0 4px 12px rgba(255, 122, 0, 0.1);
            transition: all 0.3s;
        }

        .mesa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 122, 0, 0.25);
        }

        .mesa-libre {
            border: 2px solid #2ecc71;
        }

        .mesa-ocupada {
            border: 2px solid #e74c3c;
        }

        .mesa-limpieza {
            border: 2px solid #f1c40f;
        }

        .mesa-number {
            font-weight: 700;
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .mesa-total {
            margin-top: 0.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--orange-dark);
        }

        .mesa-status .badge {
            font-size: 0.85rem;
            padding: 0.45em 0.75em;
        }

        .btn-pink {
            background-color: var(--orange-primary);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.6rem;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-pink:hover {
            background-color: var(--orange-dark);
            transform: scale(1.03);
        }

        .btn-warning {
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="header-box">
            <i class="bi bi-grid-3x3 fs-3"></i>
            <h1>Gestión de Mesas</h1>
        </div>
        
        <div class="row g-4">
            <?php foreach ($mesas as $mesa): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="mesa-card mesa-<?= $mesa->getEstado() ?>">
                        <div class="mesa-number">Mesa <?= $mesa->getNumero() ?></div>
                        <div class="mesa-status my-2">
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
                            <a href="pedido.php?mesa=<?= $mesa->getNumero() ?>" class="btn btn-pink mt-2">
                                <i class="bi bi-clipboard-check"></i> Ver Pedido
                            </a>
                        <?php elseif ($mesa->estaLibre()): ?>
                            <form method="POST" action="../controlador/mesaControlador.php">
                                <input type="hidden" name="action" value="abrir_pedido">
                                <input type="hidden" name="mesa" value="<?= $mesa->getNumero() ?>">
                                <button type="submit" class="btn btn-pink mt-2">
                                    <i class="bi bi-plus-circle"></i> Abrir Pedido
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="../controlador/mesaControlador.php">
                                <input type="hidden" name="action" value="limpiar_mesa">
                                <input type="hidden" name="mesa" value="<?= $mesa->getNumero() ?>">
                                <button type="submit" class="btn btn-warning mt-2 w-100">
                                    <i class="bi bi-check-circle"></i> Mesa Limpia
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
                </div>
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
</body>
</html>
