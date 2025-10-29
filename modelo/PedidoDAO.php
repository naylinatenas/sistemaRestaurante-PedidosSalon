<?php
require_once __DIR__ . '/../conexion/Conexion.php';

class PedidoDAO {
    private $pdo;
    public function __construct() { 
        $this->pdo = Conexion::conectar(); 
    }

    // ==========================
    // MESA
    // ==========================
    public function listarMesas() {
        $stmt = $this->pdo->query("SELECT * FROM mesa ORDER BY numero_mesa");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMesa($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM mesa WHERE id_mesa = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setEstadoMesa($id, $estado) {
        $stmt = $this->pdo->prepare("UPDATE mesa SET estado_mesa = ? WHERE id_mesa = ?");
        return $stmt->execute([$estado, $id]);
    }

    // ==========================
    // PLATO
    // ==========================
    public function listarPlatos() {
        $stmt = $this->pdo->query("SELECT * FROM plato ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearPlato($nombre, $categoria, $precio) {
        $stmt = $this->pdo->prepare("INSERT INTO plato (nombre, categoria, precio, estado) VALUES (?, ?, ?, 'activo')");
        $stmt->execute([$nombre, $categoria, $precio]);
        return $this->pdo->lastInsertId();
    }

    public function eliminarPlato($id) {
        $stmt = $this->pdo->prepare("DELETE FROM plato WHERE id_plato = ?");
        return $stmt->execute([$id]);
    }

    // ==========================
    // PEDIDO
    // ==========================
    public function abrirPedido($mesa_id, $mozo_id) {
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("INSERT INTO pedido (mesa_id, mozo_id, hora_inicio, estado_pedido, total) VALUES (?, ?, NOW(), 'abierto', 0)");
        $stmt->execute([$mesa_id, $mozo_id]);
        $pedido_id = $this->pdo->lastInsertId();

        $this->setEstadoMesa($mesa_id, 'ocupada');
        $this->pdo->commit();
        return $pedido_id;
    }

    public function obtenerPedidoAbiertoPorMesa($mesa_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pedido WHERE mesa_id = ? AND estado_pedido = 'abierto' LIMIT 1");
        $stmt->execute([$mesa_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) return null;

        $stmt2 = $this->pdo->prepare("
            SELECT d.*, p.nombre, p.precio 
            FROM detalle_pedido d 
            JOIN plato p ON p.id_plato = d.plato_id 
            WHERE d.pedido_id = ?
        ");
        $stmt2->execute([$pedido['id_pedido']]);
        $pedido['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    public function actualizarDetalles($pedido_id, $detalles) {
        $this->pdo->beginTransaction();
        $this->pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?")->execute([$pedido_id]);

        $total = 0;
        $stmtIns = $this->pdo->prepare("INSERT INTO detalle_pedido (pedido_id, plato_id, cantidad, subtotal) VALUES (?, ?, ?, ?)");
        $stmtPl = $this->pdo->prepare("SELECT precio FROM plato WHERE id_plato = ?");

        foreach ($detalles as $d) {
            $stmtPl->execute([$d['plato_id']]);
            $precio = $stmtPl->fetchColumn();
            $subtotal = round($precio * intval($d['cantidad']), 2);
            $stmtIns->execute([$pedido_id, $d['plato_id'], $d['cantidad'], $subtotal]);
            $total += $subtotal;
        }

        $this->pdo->prepare("UPDATE pedido SET total = ? WHERE id_pedido = ?")->execute([$total, $pedido_id]);
        $this->pdo->commit();
        return $total;
    }

    public function cerrarPedido($pedido_id) {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare("SELECT mesa_id FROM pedido WHERE id_pedido = ?");
        $stmt->execute([$pedido_id]);
        $mesa_id = $stmt->fetchColumn();

        $stmtD = $this->pdo->prepare("SELECT plato_id, cantidad FROM detalle_pedido WHERE pedido_id = ?");
        $stmtD->execute([$pedido_id]);
        $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        if (empty($detalles)) {
            $this->pdo->rollBack();
            throw new Exception("No hay items en el pedido");
        }

        $this->pdo->prepare("UPDATE pedido SET estado_pedido = 'cerrado', hora_cierre = NOW() WHERE id_pedido = ?")
                  ->execute([$pedido_id]);
        $this->setEstadoMesa($mesa_id, 'limpiando');

        $this->pdo->commit();
        return true;
    }

    // ==========================
    // ESTADÍSTICAS (Dashboard)
    // ==========================
    public function mesasOcupadasCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mesa WHERE estado_mesa = 'ocupada'");
        return (int)$stmt->fetchColumn();
    }

    public function ingresoHoy() {
        $stmt = $this->pdo->query("SELECT IFNULL(SUM(total), 0) FROM pedido WHERE estado_pedido = 'cerrado' AND DATE(hora_cierre) = CURDATE()");
        return (float)$stmt->fetchColumn();
    }

    public function platoMasPedidoHoy() {
        $stmt = $this->pdo->query("
            SELECT p.nombre, SUM(d.cantidad) total_cant
            FROM detalle_pedido d
            JOIN pedido pe ON pe.id_pedido = d.pedido_id
            JOIN plato p ON p.id_plato = d.plato_id
            WHERE pe.estado_pedido = 'cerrado' AND DATE(pe.hora_cierre) = CURDATE()
            GROUP BY d.plato_id
            ORDER BY total_cant DESC
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['nombre'].' ('.$row['total_cant'].')' : '-';
    }
    public function Conexion() {
    return $this->pdo;
}

}
