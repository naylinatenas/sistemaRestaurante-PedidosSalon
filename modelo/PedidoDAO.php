<?php
require_once __DIR__ . '/Mesa.php';
require_once __DIR__ . '/Plato.php';
require_once __DIR__ . '/Pedido.php';
require_once __DIR__ . '/../conexion/Conexion.php';

class PedidoDAO {

    /* ===========================
       ========== SESIÓN ==========
       =========================== */

    public static function inicializarMesas() {
        if (!isset($_SESSION['mesas'])) {
            $_SESSION['mesas'] = [];
            for ($i = 1; $i <= 6; $i++) {
                $_SESSION['mesas'][$i] = new Mesa($i);
            }
        }
    }

    public static function inicializarMenu() {
        if (!isset($_SESSION['menu'])) {
            $_SESSION['menu'] = [
                new Plato(1, 'Hamburguesa Clásica', 25.00, 'Platos Principales'),
                new Plato(2, 'Pizza Margarita', 30.00, 'Platos Principales'),
                new Plato(3, 'Ensalada César', 18.00, 'Entradas'),
                new Plato(4, 'Pasta Carbonara', 28.00, 'Platos Principales'),
                new Plato(5, 'Limonada', 8.00, 'Bebidas'),
                new Plato(6, 'Cerveza', 10.00, 'Bebidas'),
                new Plato(7, 'Helado de Vainilla', 12.00, 'Postres'),
                new Plato(8, 'Tiramisú', 15.00, 'Postres'),
            ];
        }
    }

    public static function getMesa($numero) {
        if (isset($_SESSION['mesas'][$numero])) {
            return $_SESSION['mesas'][$numero];
        } else {
            return self::getMesaPorNumeroDB($numero);
        }
    }

    public static function getTodasLasMesas() {
        return $_SESSION['mesas'];
    }

    public static function getPlatoById($id) {
        foreach ($_SESSION['menu'] as $plato) {
            if ($plato->getId() === $id) {
                return $plato;
            }
        }
        return null;
    }

    public static function getTodosLosPlatos() {
        return $_SESSION['menu'];
    }

    public static function crearPedido($mesa_numero) {
        if (!isset($_SESSION['pedidos'])) {
            $_SESSION['pedidos'] = [];
        }

        $id = uniqid('pedido_');
        $pedido = new Pedido($id, $mesa_numero);
        $_SESSION['pedidos'][$id] = $pedido;

        $mesa = self::getMesa($mesa_numero);
        if ($mesa) {
            $mesa->setEstado('ocupada');
            $mesa->setPedidoId($id);
        }

        return $pedido;
    }

    public static function getPedidoById($id) {
        if (isset($_SESSION['pedidos'][$id])) {
            return $_SESSION['pedidos'][$id];
        } else {
            return self::getPedidoByIdDB($id);
        }
    }

    public static function cerrarPedido($pedido_id) {
        $pedido = self::getPedidoById($pedido_id);
        if ($pedido) {
            $pedido->cerrar();

            // Guardar en base de datos
            $guardado = self::guardarPedidoEnDB($pedido);

            // Actualizar estado de la mesa
            $mesa = self::getMesa($pedido->getMesaNumero());
            if ($mesa) {
                $mesa->setEstado('limpiando');
            }

            unset($_SESSION['pedidos'][$pedido_id]);
            return $guardado;
        }
        return false;
    }

    public static function limpiarMesa($mesa_numero) {
        $mesa = self::getMesa($mesa_numero);
        if ($mesa) {
            $mesa->setEstado('libre');
            $mesa->setPedidoId(null);
        }
    }

    /* ===========================
       ===== BASE DE DATOS =======
       =========================== */

    private static function generarIdPedidoDB($con) {
        $stmt = $con->query("SELECT COUNT(*) + 1 as siguiente FROM pedido");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return 'PED' . str_pad($resultado['siguiente'], 3, '0', STR_PAD_LEFT);
    }

    public static function guardarPedidoEnDB($pedido) {
        try {
            $con = Conexion::conectar();
            $con->beginTransaction();

            $id_pedido_db = self::generarIdPedidoDB($con);
            $mesa_numero = $pedido->getMesaNumero();

            $stmtMesa = $con->prepare("SELECT id_mesa FROM mesa WHERE numero_mesa = :numero LIMIT 1");
            $stmtMesa->bindParam(':numero', $mesa_numero);
            $stmtMesa->execute();
            $mesaRow = $stmtMesa->fetch(PDO::FETCH_ASSOC);
            $mesa_id_db = $mesaRow ? $mesaRow['id_mesa'] : null;

            if (!$mesa_id_db) {
                throw new Exception("No se encontró la mesa (numero {$mesa_numero}) en la BD.");
            }

            $mozo_id = $_SESSION['usuario_id'] ?? null;

            $stmtInsertPedido = $con->prepare("
                INSERT INTO pedido (id_pedido, mesa_id, mozo_id, hora_inicio, hora_cierre, estado_pedido, total)
                VALUES (:id, :mesa_id, :mozo_id, :hora_inicio, :hora_cierre, :estado, :total)
            ");

            $hora_inicio = $pedido->getFecha() ?: date('Y-m-d H:i:s');
            $hora_cierre = date('Y-m-d H:i:s');
            $total = $pedido->getTotal();
            $estado = $pedido->getEstado();

            $stmtInsertPedido->bindParam(':id', $id_pedido_db);
            $stmtInsertPedido->bindParam(':mesa_id', $mesa_id_db);
            $stmtInsertPedido->bindParam(':mozo_id', $mozo_id);
            $stmtInsertPedido->bindParam(':hora_inicio', $hora_inicio);
            $stmtInsertPedido->bindParam(':hora_cierre', $hora_cierre);
            $stmtInsertPedido->bindParam(':estado', $estado);
            $stmtInsertPedido->bindParam(':total', $total);
            $stmtInsertPedido->execute();

            // Insertar detalles
            foreach ($pedido->getDetalles() as $item) {
                $stmtCount = $con->query("SELECT COUNT(*) + 1 as siguiente FROM detalle_pedido");
                $res = $stmtCount->fetch(PDO::FETCH_ASSOC);
                $id_detalle = 'D' . str_pad($res['siguiente'], 3, '0', STR_PAD_LEFT);

                $plato_id_db = $item['plato']->getId();
                $cantidad = $item['cantidad'];
                $precio_unit = $item['plato']->getPrecio();
                $subtotal = $precio_unit * $cantidad;

                $stmtInsertDetalle = $con->prepare("
                    INSERT INTO detalle_pedido (id_detalle, pedido_id, plato_id, cantidad, precio_unit, subtotal)
                    VALUES (:id_detalle, :pedido_id, :plato_id, :cantidad, :precio_unit, :subtotal)
                ");
                $stmtInsertDetalle->bindParam(':id_detalle', $id_detalle);
                $stmtInsertDetalle->bindParam(':pedido_id', $id_pedido_db);
                $stmtInsertDetalle->bindParam(':plato_id', $plato_id_db);
                $stmtInsertDetalle->bindParam(':cantidad', $cantidad);
                $stmtInsertDetalle->bindParam(':precio_unit', $precio_unit);
                $stmtInsertDetalle->bindParam(':subtotal', $subtotal);
                $stmtInsertDetalle->execute();
            }

            // Actualizar mesa en BD
            $stmtUpdateMesa = $con->prepare("UPDATE mesa SET estado_mesa = 'limpiando' WHERE id_mesa = :id");
            $stmtUpdateMesa->bindParam(':id', $mesa_id_db);
            $stmtUpdateMesa->execute();

            $con->commit();
            return true;

        } catch (Exception $e) {
            if (isset($con) && $con->inTransaction()) $con->rollBack();
            error_log("Error al guardar pedido: " . $e->getMessage());
            return false;
        }
    }

    /* ===========================
       ==== FUNCIONES HISTORIAL ===
       =========================== */

    public static function obtenerTodosLosPedidosDB() {
        try {
            $con = Conexion::conectar();
            $stmt = $con->query("
                SELECT p.*, m.numero_mesa, u.nombre AS mozo_nombre
                FROM pedido p
                INNER JOIN mesa m ON p.mesa_id = m.id_mesa
                LEFT JOIN usuario u ON p.mozo_id = u.id_usuario
                ORDER BY p.hora_inicio DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function getTodosLosPedidos() {
        return self::obtenerTodosLosPedidosDB();
    }

    public static function obtenerPedidoPorIdDB($id_pedido) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("
                SELECT p.*, m.numero_mesa, u.nombre AS mozo_nombre
                FROM pedido p
                INNER JOIN mesa m ON p.mesa_id = m.id_mesa
                LEFT JOIN usuario u ON p.mozo_id = u.id_usuario
                WHERE p.id_pedido = :id
            ");
            $stmt->bindParam(':id', $id_pedido);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function obtenerDetallesPedidoDB($id_pedido) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("
                SELECT dp.*, pl.nombre AS plato_nombre, dp.precio_unit AS precio
                FROM detalle_pedido dp
                INNER JOIN plato pl ON dp.plato_id = pl.id_plato
                WHERE dp.pedido_id = :pedido
            ");
            $stmt->bindParam(':pedido', $id_pedido);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function eliminarPedidoDB($id_pedido) {
        try {
            $con = Conexion::conectar();
            $con->beginTransaction();

            $stmtPedido = $con->prepare("SELECT * FROM pedido WHERE id_pedido = :id");
            $stmtPedido->bindParam(':id', $id_pedido);
            $stmtPedido->execute();
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) {
                $con->rollBack();
                return false;
            }

            $stmt = $con->prepare("DELETE FROM detalle_pedido WHERE pedido_id = :id");
            $stmt->bindParam(':id', $id_pedido);
            $stmt->execute();

            $stmt2 = $con->prepare("DELETE FROM pedido WHERE id_pedido = :id");
            $stmt2->bindParam(':id', $id_pedido);
            $stmt2->execute();

            $stmtUpdateMesa = $con->prepare("UPDATE mesa SET estado_mesa = 'libre' WHERE id_mesa = :id_mesa");
            $stmtUpdateMesa->bindParam(':id_mesa', $pedido['mesa_id']);
            $stmtUpdateMesa->execute();

            $con->commit();
            return true;
        } catch (Exception $e) {
            if (isset($con) && $con->inTransaction()) $con->rollBack();
            error_log("Error al eliminar pedido: " . $e->getMessage());
            return false;
        }
    }

    /* ===========================
       === COMPATIBILIDAD pedido.php ===
       =========================== */

    public static function getPedidoByIdDB($pedido_id) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("
                SELECT p.*, m.numero_mesa
                FROM pedido p
                INNER JOIN mesa m ON p.mesa_id = m.id_mesa
                WHERE p.id_pedido = :id
            ");
            $stmt->bindParam(':id', $pedido_id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;

            $pedido = new Pedido($data['id_pedido'], $data['numero_mesa']);
            $pedido->setEstado($data['estado_pedido']);
            $pedido->setFecha($data['hora_inicio']);
            $pedido->setTotal($data['total']);

            $detalles = self::obtenerDetallesPedidoDB($pedido_id);
            foreach ($detalles as $det) {
                $plato = new Plato(
                    $det['plato_id'],
                    $det['plato_nombre'],
                    $det['precio'],
                    'Desconocida'
                );
                $pedido->agregarPlato($plato, $det['cantidad']);
            }

            return $pedido;
        } catch (Exception $e) {
            error_log("Error en getPedidoByIdDB: " . $e->getMessage());
            return null;
        }
    }

    public static function getMesaPorNumeroDB($numero_mesa) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("SELECT * FROM mesa WHERE numero_mesa = :numero");
            $stmt->bindParam(':numero', $numero_mesa);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;

            $mesa = new Mesa($data['numero_mesa']);
            $mesa->setEstado($data['estado_mesa']);
            if (isset($data['id_pedido_activo'])) {
                $mesa->setPedidoId($data['id_pedido_activo']);
            }

            return $mesa;
        } catch (Exception $e) {
            error_log("Error al obtener mesa por número: " . $e->getMessage());
            return null;
        }
    }
}
?>
