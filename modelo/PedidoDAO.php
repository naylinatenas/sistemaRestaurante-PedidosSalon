<?php
require_once __DIR__.'/../conexion/Conexion.php';

class PedidoDAO {
    private $pdo;
    public function __construct(){ $this->pdo = Conexion::conectar(); }

    // Mesas
    public function listarMesas(){
        $stmt = $this->pdo->query("SELECT * FROM mesas ORDER BY numero");
        return $stmt->fetchAll();
    }
    public function getMesa($id){
        $stmt = $this->pdo->prepare("SELECT * FROM mesas WHERE id = ?");
        $stmt->execute([$id]); return $stmt->fetch();
    }
    public function setEstadoMesa($id, $estado){
        $stmt = $this->pdo->prepare("UPDATE mesas SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado,$id]);
    }

    // Platos
    public function listarPlatos(){
        $stmt = $this->pdo->query("SELECT * FROM platos ORDER BY nombre");
        return $stmt->fetchAll();
    }
    public function crearPlato($nombre,$categoria,$precio){
        $stmt = $this->pdo->prepare("INSERT INTO platos (nombre,categoria,precio,veces) VALUES (?,?,?,0)");
        $stmt->execute([$nombre,$categoria,$precio]); return $this->pdo->lastInsertId();
    }
    public function eliminarPlato($id){
        $stmt = $this->pdo->prepare("DELETE FROM platos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Pedidos
    public function abrirPedido($mesa_id, $mozo='mozo_demo'){
        // crear pedido y marcar mesa ocupada
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("INSERT INTO pedidos (mesa_id,mozo,hora_inicio,estado,total) VALUES (?,? , NOW(),'abierto',0)");
        $stmt->execute([$mesa_id,$mozo]);
        $pedido_id = $this->pdo->lastInsertId();
        $this->setEstadoMesa($mesa_id,'ocupada');
        $this->pdo->commit();
        return $pedido_id;
    }
    public function obtenerPedidoAbiertoPorMesa($mesa_id){
        $stmt = $this->pdo->prepare("SELECT * FROM pedidos WHERE mesa_id = ? AND estado = 'abierto' LIMIT 1");
        $stmt->execute([$mesa_id]); $p = $stmt->fetch();
        if(!$p) return null;
        // detalles
        $stmt2 = $this->pdo->prepare("SELECT d.*, pl.nombre, pl.precio FROM detalle_pedido d JOIN platos pl ON pl.id = d.plato_id WHERE d.pedido_id = ?");
        $stmt2->execute([$p['id']]); $p['detalles'] = $stmt2->fetchAll();
        return $p;
    }
    public function actualizarDetalles($pedido_id, $detalles){
        // $detalles = [['plato_id'=>x,'cantidad'=>y],...]
        $this->pdo->beginTransaction();
        // borrar detalles actuales y reinsertar
        $this->pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?")->execute([$pedido_id]);
        $total = 0;
        $stmtIns = $this->pdo->prepare("INSERT INTO detalle_pedido (pedido_id,plato_id,cantidad,subtotal) VALUES (?,?,?,?)");
        $stmtPl = $this->pdo->prepare("SELECT precio FROM platos WHERE id = ?");
        foreach($detalles as $d){
            $stmtPl->execute([$d['plato_id']]); $precio = $stmtPl->fetchColumn();
            $subtotal = round($precio * intval($d['cantidad']),2);
            $stmtIns->execute([$pedido_id, $d['plato_id'], $d['cantidad'], $subtotal]);
            $total += $subtotal;
        }
        $this->pdo->prepare("UPDATE pedidos SET total = ? WHERE id = ?")->execute([$total, $pedido_id]);
        $this->pdo->commit();
        return $total;
    }
    public function cerrarPedido($pedido_id){
        // cambiar estado pedido, marcar mesa limpiando y aumentar 'veces' de platos
        $this->pdo->beginTransaction();
        // obtener detalles y mesa
        $stmt = $this->pdo->prepare("SELECT mesa_id FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]); $mesa_id = $stmt->fetchColumn();
        $stmtD = $this->pdo->prepare("SELECT plato_id, cantidad FROM detalle_pedido WHERE pedido_id = ?");
        $stmtD->execute([$pedido_id]); $detalles = $stmtD->fetchAll();

        if(empty($detalles)){
            $this->pdo->rollBack(); throw new Exception("No hay items en el pedido");
        }
        // actualizar veces
        $stmtUp = $this->pdo->prepare("UPDATE platos SET veces = veces + ? WHERE id = ?");
        foreach($detalles as $d){ $stmtUp->execute([$d['cantidad'],$d['plato_id']]); }

        // cerrar pedido
        $this->pdo->prepare("UPDATE pedidos SET estado = 'cerrado', hora_cierre = NOW() WHERE id = ?")->execute([$pedido_id]);
        // mesa -> limpiando
        $this->setEstadoMesa($mesa_id,'limpiando');

        $this->pdo->commit();
        return true;
    }

    // Estadísticas
    public function mesasOcupadasCount(){
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mesas WHERE estado = 'ocupada'");
        return (int)$stmt->fetchColumn();
    }
    public function ingresoHoy(){
        $stmt = $this->pdo->query("SELECT IFNULL(SUM(total),0) FROM pedidos WHERE estado='cerrado' AND DATE(hora_cierre)=CURDATE()");
        return (float)$stmt->fetchColumn();
    }
    public function platoMasPedidoHoy(){
        $stmt = $this->pdo->query("
            SELECT p.nombre, SUM(d.cantidad) total_cant
            FROM detalle_pedido d
            JOIN pedidos pe ON pe.id = d.pedido_id
            JOIN platos p ON p.id = d.plato_id
            WHERE pe.estado='cerrado' AND DATE(pe.hora_cierre)=CURDATE()
            GROUP BY d.plato_id ORDER BY total_cant DESC LIMIT 1
        ");
        $row = $stmt->fetch();
        return $row ? $row['nombre'].' ('.$row['total_cant'].')' : '-';
    }
}
