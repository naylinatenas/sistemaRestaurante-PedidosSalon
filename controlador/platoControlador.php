<?php
require_once __DIR__.'/../modelo/PedidoDAO.php';
$dao = new PedidoDAO();
$method = $_SERVER['REQUEST_METHOD'];
if($method === 'GET'){
    echo json_encode($dao->listarPlatos()); exit;
}
if($method === 'POST'){
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input){ // fallback from form post
        $input = $_POST;
    }
    if(isset($input['nombre'])){
        $id = $dao->crearPlato($input['nombre'],$input['categoria'],$input['precio']);
        echo json_encode(['ok'=>true,'id'=>$id]); exit;
    }
}
if($method === 'DELETE'){
    parse_str(file_get_contents("php://input"), $delVars);
    $id = $delVars['id'] ?? null;
    if($id){ $dao->eliminarPlato($id); echo json_encode(['ok'=>true]); exit; }
}
