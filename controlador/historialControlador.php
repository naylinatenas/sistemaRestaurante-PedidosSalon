<?php
require_once __DIR__ . '/../modelo/PedidoDAO.php';
session_start();

if (!isset($_SESSION['usuario']) ) {
    header('Location: ../vista/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'eliminar_pedido':
            $pedido_id = $_POST['pedido_id'] ?? '';
            if ($pedido_id) {
                $ok = PedidoDAO::eliminarPedidoDB($pedido_id);
                // podrías setear mensaje de sesión según $ok
            }
            break;
    }
}

header('Location: ../vista/historialPedidos.php');
exit;
?>
