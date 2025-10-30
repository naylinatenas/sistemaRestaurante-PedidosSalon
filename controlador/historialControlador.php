<?php
require_once __DIR__ . '/../modelo/PedidoDAO.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../vista/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'eliminar_pedido':
            $pedido_id = $_POST['pedido_id'] ?? '';
            if ($pedido_id) {
                try {
                    // Asegúrate de que este método existe en PedidoDAO
                    $resultado = PedidoDAO::eliminarPedidoDB($pedido_id);
                    
                    if ($resultado) {
                        $_SESSION['mensaje'] = 'Pedido eliminado correctamente';
                        $_SESSION['tipo_mensaje'] = 'success';
                    } else {
                        $_SESSION['mensaje'] = 'No se pudo eliminar el pedido';
                        $_SESSION['tipo_mensaje'] = 'danger';
                    }
                } catch (Exception $e) {
                    $_SESSION['mensaje'] = 'Error al eliminar: ' . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = 'danger';
                }
            }
            break;
    }
}

header('Location: ../vista/historialPedidos.php');
exit;
?>