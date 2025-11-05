<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../modelo/PlatoDAO.php';
ob_start();

$dao = new PlatoDAO();
$platos = $dao->listarPlatos();
?>

<style>
    :root {
        --orange-primary: #ff8c00;
        --orange-dark: #e67e00;
        --orange-light: #fff7ec;
        --gradient-orange: linear-gradient(135deg, #ff8c00, #ffb347);
        --bg-light: #fffaf5;
    }

    body {
        background: var(--bg-light);
        font-family: 'Poppins', sans-serif;
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .header {
        background: var(--gradient-orange);
        color: white;
        padding: 2rem 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(255, 140, 0, 0.3);
    }

    .header h1 {
        font-weight: 700;
        font-size: 2rem;
    }

    .header p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .card {
        border: none;
        border-radius: 18px;
        background: white;
        box-shadow: 0 4px 15px rgba(255, 136, 0, 0.15);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 25px rgba(255, 140, 0, 0.25);
    }

    .card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--gradient-orange);
    }

    .card i {
        font-size: 2.5rem;
        color: var(--orange-dark);
        background: var(--orange-light);
        padding: 1rem;
        border-radius: 50%;
        margin-bottom: 1rem;
    }

    .card h5 {
        color: var(--orange-dark);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .card p {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }

    .footer {
        text-align: center;
        color: #888;
        font-size: 0.9rem;
        margin-top: 3rem;
    }

    .btn-orange {
        background: var(--gradient-orange);
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        padding: 10px 20px;
        transition: 0.3s;
    }

    .btn-orange:hover {
        background: linear-gradient(135deg, #e67e00, #ff9900);
        transform: scale(1.05);
    }
</style>

<div class="main-content">
    <div class="container-wrapper">
        <div class="header d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center" style="gap: 1rem;">
                <i class="bi bi-bar-chart-line-fill"></i>
                <h2>Platos</h2>
            </div>
            <div>
                <button id="crearPlato" type="button" class="btn btn-light d-flex align-items-center ">
                    <i class="bi bi-plus"></i>
                    <span>Crear</span>
                </button>
            </div>
        </div>

        <div class="cocntent">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($platos as $p): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($p['id_plato']); ?></strong></td>
                            <td><?= htmlspecialchars($p['nombre']); ?></td>
                            <td><?= htmlspecialchars($p['categoria']); ?></td>
                            <td><strong>S/ <?= number_format($p['precio'], 2); ?></strong></td>
                            <td>
                                <span class="<?= $p['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo'; ?>">
                                    <?= ucfirst($p['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm me-1"
                                    data-bs-modal="edit"
                                    data-id="<?= $p['id_plato'] ?>"
                                    data-nombre="<?= $p['nombre'] ?>"
                                    data-categoria="<?= $p['categoria'] ?>"
                                    data-precio="<?= $p['precio'] ?>"
                                    data-estado="<?= $p['estado'] ?>">
                                    <i class="bi bi-pen-fill"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" data-bs-modal="delete" data-id="<?= $p['id_plato'] ?>">
                                    <i class="bi bi-trash2-fill"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formularioModalPlato" tabindex="100" aria-labelledby="formularioModalPlatoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="formularioModalPlatoLabel">Titulo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formularioPlato" class="row g-3" action="">
                    <div class="col-md-4">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" placeholder="P001" required>
                    </div>
                    <div class="col-md-8">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="col-md-6">
                        <label for="categoria" class="form-label">Categoría</label>
                        <input type="text" class="form-control" id="categoria" required>
                    </div>
                    <div class="col-md-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" class="form-control" id="precio" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnGuardar" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
if ($_SESSION['rol'] === 'admin') {
    include_once "dashboard-admin.php";
} else {
    include_once "dashboard-mozo.php";
}
?>