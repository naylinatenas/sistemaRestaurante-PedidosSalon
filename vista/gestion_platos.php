<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../modelo/PlatoDAO.php';
require_once __DIR__ . '/../conexion/Conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit();
} 

$dao = new PlatoDAO();
$platos = $dao->listarPlatos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Platos - Restaurante</title>

    <!-- Bootstrap / Icons / Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/dashboard_style.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-utensils main-icon"></i>
        <h3>Sistema Restaurante</h3>
        <div class="user-info">
            <i class="fa-solid fa-user-shield"></i>
            <span><?= htmlspecialchars($_SESSION['usuario']); ?></span>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="dashboard-admin.php">
            <i class="fa-solid fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="gestion_platos.php" class="active">
            <i class="fa-solid fa-utensils"></i>
            <span>Gestión de Platos</span>
        </a>
    </div>

    <form action="../controlador/logout.php" method="post">
        <button type="submit" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </button>
    </form>
</div>

<!-- Main -->
<div class="main-content">
    <div class="section-header">
        <h2><i class="fa-solid fa-utensils"></i> Gestión de Platos</h2>
        <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalPlato" onclick="resetForm()">
            <i class="fa-solid fa-plus"></i> Nuevo Plato
        </button>
    </div>

    <div id="tablaPlatos" class="table-container">
        <?php include __DIR__ . '/tabla_platos.php'; ?>
    </div>
</div>

<!-- Modal Plato -->
<div class="modal fade" id="modalPlato" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formPlato" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-utensils me-2"></i>
                    <span id="modalTitleText">Nuevo Plato</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="accion" id="accion" value="crear">
                <input type="hidden" name="id_plato_original" id="id_plato_original">

                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-key"></i> ID Plato</label>
                    <input type="text" name="id_plato" id="id_plato" class="form-control" placeholder="Ej: P004 (opcional)">
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-signature"></i> Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Ceviche de pescado" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-list"></i> Categoría</label>
                    <select name="categoria" id="categoria" class="form-select">
                        <option value="Entrada">Entrada</option>
                        <option value="Plato Fuerte">Plato Fuerte</option>
                        <option value="Postre">Postre</option>
                        <option value="Bebida">Bebida</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-dollar-sign"></i> Precio (S/)</label>
                    <input type="number" name="precio" id="precio" step="0.01" class="form-control" placeholder="0.00" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-toggle-on"></i> Estado</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-custom">
                    <i class="fa-solid fa-save me-2"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const modal = new bootstrap.Modal(document.getElementById('modalPlato'));

function resetForm() {
    document.getElementById('modalTitleText').textContent = 'Nuevo Plato';
    document.getElementById('accion').value = 'crear';
    document.getElementById('id_plato_original').value = '';
    document.getElementById('id_plato').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('categoria').value = 'Entrada';
    document.getElementById('precio').value = '';
    document.getElementById('estado').value = 'activo';
}

function editarPlato(p) {
    document.getElementById('modalTitleText').textContent = 'Editar Plato';
    document.getElementById('accion').value = 'editar';
    document.getElementById('id_plato_original').value = p.id_plato;
    document.getElementById('id_plato').value = p.id_plato;
    document.getElementById('nombre').value = p.nombre;
    document.getElementById('categoria').value = p.categoria;
    document.getElementById('precio').value = p.precio;
    document.getElementById('estado').value = p.estado;
}

document.getElementById('formPlato').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const response = await fetch('../controlador/platoControlador.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            // Recargar tabla
            const tabla = await fetch('tabla_platos.php');
            document.getElementById('tablaPlatos').innerHTML = await tabla.text();

            modal.hide();
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
            });
        }

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error.message
        });
    }
});
</script>
</body>
</html>
