<?php
require_once '../modelo/PedidoDAO.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'mozo') {
    header("Location: ../vista/login.php");
    exit();
}

// Verificar que exista usuario_id en sesión
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['error'] = 'Error de sesión. Por favor, inicie sesión nuevamente.';
    header("Location: ../controlador/logout.php");
    exit();
}

// Si el método es POST, procesar acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    switch ($action) {

        // ABRIR PEDIDO
        case 'abrir_pedido':
            $mesa_numero = intval($_POST['mesa'] ?? 0);

            if ($mesa_numero <= 0) {
                $_SESSION['error'] = 'Número de mesa inválido.';
                header('Location: ../vista/mesas.php');
                exit();
            }

            $pedido_id = PedidoDAO::crearPedidoDB($mesa_numero);

            if ($pedido_id) {
                $_SESSION['mensaje'] = 'Pedido abierto correctamente.';
                header("Location: ../vista/pedido.php?mesa={$mesa_numero}");
                exit();
            } else {
                $_SESSION['error'] = 'No se pudo crear el pedido. Verifique su sesión.';
                header('Location: ../vista/mesas.php');
                exit();
            }
            break;

        // CERRAR PEDIDO
        case 'cerrar_pedido':
            $pedido_id = $_POST['pedido_id'] ?? '';

            if (empty($pedido_id)) {
                $_SESSION['error'] = 'Falta el ID del pedido.';
                header('Location: ../vista/mesas.php');
                exit();
            }

            $resultado = PedidoDAO::cerrarPedidoDB($pedido_id);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Pedido cerrado correctamente.';
            } else {
                $_SESSION['error'] = 'No se pudo cerrar el pedido.';
            }

            header('Location: ../vista/mesas.php');
            exit();

        // LIMPIAR MESA
        case 'limpiar_mesa':
            $mesa_numero = intval($_POST['mesa'] ?? 0);

            if ($mesa_numero <= 0) {
                $_SESSION['error'] = 'Número de mesa inválido.';
                header('Location: ../vista/mesas.php');
                exit();
            }

            $resultado = PedidoDAO::limpiarMesaDB($mesa_numero);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Mesa limpiada correctamente.';
            } else {
                $_SESSION['error'] = 'No se pudo limpiar la mesa.';
            }

            header('Location: ../vista/mesas.php');
            exit();

        // ELIMINAR PEDIDO
        case 'eliminar_pedido':
            $pedido_id = $_POST['pedido_id'] ?? '';

            if (empty($pedido_id)) {
                $_SESSION['error'] = 'Falta el ID del pedido.';
                header('Location: ../vista/mesas.php');
                exit();
            }

            $resultado = PedidoDAO::eliminarPedidoDB($pedido_id);

            if ($resultado) {
                $_SESSION['mensaje'] = 'Pedido eliminado correctamente.';
            } else {
                $_SESSION['error'] = 'No se pudo eliminar el pedido.';
            }

            header('Location: ../vista/mesas.php');
            exit();

        // ACCIÓN DESCONOCIDA
        default:
            $_SESSION['error'] = 'Acción no válida.';
            header('Location: ../vista/mesas.php');
            exit();
    }
}

// Si se accede directamente sin POST
header('Location: ../vista/mesas.php');
exit();
?>
