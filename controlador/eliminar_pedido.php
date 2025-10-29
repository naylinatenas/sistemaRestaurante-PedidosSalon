<?php
session_start();
require_once '../conexion/Conexion.php';
require_once '../modelo/PedidoDAO.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Obtener datos del request
$data = json_decode(file_get_contents('php://input'), true);
$pedido_id = isset($data['pedido_id']) ? intval($data['pedido_id']) : 0;

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit();
}

try {
    $conexion = Conexion::getInstancia()->getConexion();
    $pedidoDAO = new PedidoDAO($conexion);
    
    $resultado = $pedidoDAO->eliminarPedido($pedido_id);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Pedido eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el pedido']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
