<?php
// 🔹 Cargar clases antes del session_start()
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

// ✅ Obtener menú desde PedidoDAO
$menu = PedidoDAO::getTodosLosPlatos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff7f0;
            color: #333;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #ff8c00, #ffb347);
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            color: white;
        }

        .sidebar h3 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 40px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            margin-left: 270px;
            padding: 40px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(255, 140, 0, 0.2);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .btn-orange {
            background: linear-gradient(90deg, #ff8c00, #ffb347);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-orange:hover {
            background: linear-gradient(90deg, #e67e00, #ffa94d);
        }

        table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        th {
            background-color: #ff8c00;
            color: white;
            text-align: center;
        }

        td {
            text-align: center;
            vertical-align: middle;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar h2 {
            color: #ff8c00;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h3>🍊 ADMIN</h3>
        <a href="#"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a href="#"><i class="bi bi-people-fill me-2"></i>Empleados</a>
        <a href="#"><i class="bi bi-cup-hot me-2"></i>Pedidos</a>
        <a href="#"><i class="bi bi-egg-fried me-2"></i>Platos</a>
        <a href="#"><i class="bi bi-gear me-2"></i>Configuración</a>
        <hr style="border-color:white;">
        <a href="../controlador/logout.php" class="text-white"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="topbar">
            <h2>Gestión de Platos</h2>
            <button class="btn-orange" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="bi bi-plus-circle"></i> Nuevo Plato
            </button>
        </div>

        <!-- Tabla de Platos -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio (S/)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu as $plato): ?>
                    <tr>
                        <td><?php echo $plato->getId(); ?></td>
                        <td><?php echo htmlspecialchars($plato->getNombre()); ?></td>
                        <td><?php echo htmlspecialchars($plato->getCategoria()); ?></td>
                        <td><?php echo number_format($plato->getPrecio(), 2); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para agregar plato -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="../controlador/platoControlador.php" method="POST" class="modal-content">
                <div class="modal-header" style="background:#ff8c00; color:white;">
                    <h5 class="modal-title">Agregar Nuevo Plato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nombre del Plato</label>
                    <input type="text" name="nombre" class="form-control" required>

                    <label class="form-label mt-3">Categoría</label>
                    <select name="categoria" class="form-select" required>
                        <option value="Platos Principales">Platos Principales</option>
                        <option value="Entradas">Entradas</option>
                        <option value="Bebidas">Bebidas</option>
                        <option value="Postres">Postres</option>
                    </select>

                    <label class="form-label mt-3">Precio (S/)</label>
                    <input type="number" step="0.01" name="precio" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="accion" value="agregar" class="btn-orange">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
