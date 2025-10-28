<?php
require_once __DIR__.'/../modelo/PedidoDAO.php';
$dao = new PedidoDAO();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
if($action === 'abrir'){
    $mesa_id = $_POST['mesa_id'];
    $id = $dao->abrirPedido($mesa_id);
    echo json_encode(['ok'=>true,'pedido_id'=>$id]); exit;
}
if($action === 'cerrar'){
    $mesa_id = $_POST['mesa_id'];
    // buscar pedido abierto
    $pedido = $dao->obtenerPedidoAbiertoPorMesa($mesa_id);
    if(!$pedido){ echo json_encode(['error'=>'no_open']); exit;}
    try{
        $dao->cerrarPedido($pedido['id']);
        echo json_encode(['ok'=>true]); exit;
    }catch(Exception $e){
        echo json_encode(['error'=>$e->getMessage()]); exit;
    }
}
if($action === 'list'){
    echo json_encode($dao->listarMesas()); exit;
}
