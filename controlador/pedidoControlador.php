<?php
require_once '../modelo/PedidoDAO.php';
session_start();

// ✅ Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}

// ✅ Solo aceptar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/mesas.php');
    exit();
}

$action = $_POST['action'] ?? '';
$mesa = $_POST['mesa'] ?? '';

switch ($action) {

    // 🟢 AGREGAR PLATO AL PEDIDO
    case 'agregar_plato':
        $pedido_id = $_POST['pedido_id'] ?? '';
        $plato_id = trim($_POST['plato_id'] ?? '');
        $cantidad = intval($_POST['cantidad'] ?? 0);

        if ($pedido_id && $plato_id && $cantidad > 0) {
            $resultado = PedidoDAO::agregarPlatoAPedidoDB($pedido_id, $plato_id, $cantidad);
            $_SESSION[$resultado ? 'mensaje' : 'error'] = $resultado 
                ? 'Plato agregado correctamente.' 
                : 'No se pudo agregar el plato al pedido.';
        } else {
            $_SESSION['error'] = 'Datos inválidos para agregar plato.';
        }
        break;

    // 🔴 ELIMINAR PLATO DEL PEDIDO
    case 'eliminar_plato':
        $pedido_id = $_POST['pedido_id'] ?? '';
        $plato_id = $_POST['plato_id'] ?? '';

        if ($pedido_id && $plato_id) {
            $resultado = PedidoDAO::eliminarPlatoDePedidoDB($pedido_id, $plato_id);
            $_SESSION[$resultado ? 'mensaje' : 'error'] = $resultado
                ? 'Plato eliminado correctamente.'
                : 'No se pudo eliminar el plato.';
        } else {
            $_SESSION['error'] = 'Datos incompletos para eliminar el plato.';
        }
        break;

    // 🟠 CERRAR PEDIDO
    case 'cerrar_pedido':
        $pedido_id = $_POST['pedido_id'] ?? '';

        if ($pedido_id) {
            $resultado = PedidoDAO::cerrarPedidoDB($pedido_id);
            if ($resultado) {
                $_SESSION['mensaje'] = 'Pedido cerrado correctamente.';
                header('Location: ../vista/mesas.php');
                exit;
            } else {
                $_SESSION['error'] = 'No se pudo cerrar el pedido.';
            }
        } else {
            $_SESSION['error'] = 'Falta el ID del pedido para cerrar.';
        }
        break;

    // 🔵 ELIMINAR PEDIDO COMPLETO
    case 'eliminar_pedido':
        $pedido_id = $_POST['pedido_id'] ?? '';
        if ($pedido_id) {
            $resultado = PedidoDAO::eliminarPedidoDB($pedido_id);
            if ($resultado) {
                $_SESSION['mensaje'] = 'Pedido eliminado correctamente.';
                // 🔁 Redirigir a la lista de mesas porque el pedido ya no existe
                header('Location: ../vista/mesas.php');
                exit;
            } else {
                $_SESSION['error'] = 'No se pudo eliminar el pedido.';
            }
        } else {
            $_SESSION['error'] = 'Falta el ID del pedido para eliminar.';
        }
        break;


    // ⚫ ACCIÓN DESCONOCIDA
    default:
        $_SESSION['error'] = 'Acción no válida.';
        break;
}

// ✅ Redirigir según la mesa
if ($mesa) {
    header('Location: ../vista/pedido.php?mesa=' . urlencode($mesa));
} else {
    header('Location: ../vista/mesas.php');
}
exit;
?>
