<?php
require_once __DIR__.'/../modelo/PedidoDAO.php';
$dao = new PedidoDAO();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? null;

if($action === 'getOpen'){
    $mesa = $_GET['mesa'] ?? null;
    if(!$mesa) { echo json_encode(null); exit; }
    $p = $dao->obtenerPedidoAbiertoPorMesa($mesa);
    echo json_encode($p); exit;
}

if($action === 'update'){
    $mesa_id = $input['mesa_id'];
    $items = $input['items'] ?? [];
    // conseguir pedido abierto
    $p = $dao->obtenerPedidoAbiertoPorMesa($mesa_id);
    if(!$p){ echo json_encode(['error'=>'no_open']); exit; }
    // convertir items a estructura requerida
    $det = array_map(function($it){ return ['plato_id'=>$it['plato_id'],'cantidad'=>intval($it['cantidad'])]; }, $items);
    $total = $dao->actualizarDetalles($p['id'], $det);
    echo json_encode(['ok'=>true,'total'=>$total]); exit;
}

if($action === 'abrir'){ // proxy a mesaControlador: soporte POST desde JS
    $mesa_id = $input['mesa_id'];
    $id = $dao->abrirPedido($mesa_id);
    echo json_encode(['ok'=>true,'pedido_id'=>$id]); exit;
}

if($action === 'cerrar'){
    $mesa_id = $input['mesa_id'];
    $p = $dao->obtenerPedidoAbiertoPorMesa($mesa_id);
    if(!$p){ echo json_encode(['error'=>'no_open']); exit; }
    try{
        $dao->cerrarPedido($p['id']);
        echo json_encode(['ok'=>true]); exit;
    }catch(Exception $e){
        echo json_encode(['error'=>$e->getMessage()]); exit;
    }
}

// stats
if($action === 'stats'){
    echo json_encode([
        'mesas_ocupadas' => $dao->mesasOcupadasCount(),
        'ingreso_hoy'    => $dao->ingresoHoy(),
        'plato_mas'      => $dao->platoMasPedidoHoy()
    ]);
    exit;
}
