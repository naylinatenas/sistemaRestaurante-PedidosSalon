<?php
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';
session_start();

// Resto del código igual...

require_once '../modelo/PedidoDAO.php';

// Inicializar datos
PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'agregar_plato':
            $pedido_id = $_POST['pedido_id'] ?? '';
            $plato_id = intval($_POST['plato_id']);
            $cantidad = intval($_POST['cantidad']);
            
            $pedido = PedidoDAO::getPedidoById($pedido_id);
            $plato = PedidoDAO::getPlatoById($plato_id);
            
            if ($pedido && $plato && $pedido->getEstado() === 'abierto') {
                $pedido->agregarItem($plato, $cantidad);
            }
            break;
            
        case 'cerrar_pedido':
            $pedido_id = $_POST['pedido_id'] ?? '';
            PedidoDAO::cerrarPedido($pedido_id);
            break;
    }
    
    $mesa = $_POST['mesa'] ?? '';
    header('Location: ../vista/pedido.php?mesa=' . $mesa);
    exit;
}

header('Location: ../vista/mesas.php');
exit;
?>