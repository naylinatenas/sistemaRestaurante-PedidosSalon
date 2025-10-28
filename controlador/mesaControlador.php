<?php
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';
session_start();
require_once '../modelo/PedidoDAO.php';

// Inicializar datos
PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'abrir_pedido':
            $mesa_numero = intval($_POST['mesa']);
            $mesa = PedidoDAO::getMesa($mesa_numero);
            
            if ($mesa && $mesa->estaLibre()) {
                PedidoDAO::crearPedido($mesa_numero);
            }
            break;
            
        case 'limpiar_mesa':
            $mesa_numero = intval($_POST['mesa']);
            PedidoDAO::limpiarMesa($mesa_numero);
            break;
    }
    
    header('Location: ../vista/mesas.php');
    exit;
}

header('Location: ../vista/mesas.php');
exit;
?>