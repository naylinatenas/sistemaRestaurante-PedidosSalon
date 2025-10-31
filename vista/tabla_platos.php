<?php
require_once __DIR__ . '/../modelo/PlatoDAO.php';
$dao = new PlatoDAO();
$platos = $dao->listarPlatos();
?>
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
                        onclick='editarPlato(<?= json_encode($p); ?>)'
                        data-bs-toggle="modal" data-bs-target="#modalPlato">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="eliminarPlato('<?= $p['id_plato']; ?>')">
                        <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<script>
async function eliminarPlato(id) {
    const confirm = await Swal.fire({
        title: '¿Eliminar plato?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirm.isConfirmed) return;

    const response = await fetch(`../controlador/platoControlador.php?accion=eliminar&id=${id}`);
    const result = await response.json();

    if (result.status === 'success') {
        const tabla = await fetch('tabla_platos.php');
        document.getElementById('tablaPlatos').innerHTML = await tabla.text();
        Swal.fire({
            icon: 'success',
            title: 'Eliminado',
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
}
</script>
