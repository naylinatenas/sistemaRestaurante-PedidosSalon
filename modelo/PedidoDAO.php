<?php
require_once __DIR__ . '/Mesa.php';
require_once __DIR__ . '/Plato.php';
require_once __DIR__ . '/Pedido.php';
require_once __DIR__ . '/../conexion/Conexion.php';

class PedidoDAO {

    private $pdo;
    public function __construct() { 
        $this->pdo = Conexion::conectar(); 
    }

    /* ===========================
       ===== CREAR PEDIDO EN BD ====
       =========================== */
    
    public static function crearPedidoDB($mesa_numero) {
        try {
            $con = Conexion::conectar();
            $con->beginTransaction();

            // 1. Obtener id_mesa
            $stmtMesa = $con->prepare("SELECT id_mesa FROM mesa WHERE numero_mesa = :numero LIMIT 1");
            $stmtMesa->bindParam(':numero', $mesa_numero);
            $stmtMesa->execute();
            $mesaRow = $stmtMesa->fetch(PDO::FETCH_ASSOC);
            
            if (!$mesaRow) {
                throw new Exception("Mesa no encontrada");
            }
            
            $mesa_id_db = $mesaRow['id_mesa'];

            // 2. Generar ID del pedido
            $id_pedido = self::generarIdPedidoDB($con);

            // 3. Obtener mozo_id de sesión
            $mozo_id = $_SESSION['usuario_id'] ?? null;
            
            if (!$mozo_id) {
                throw new Exception("Usuario no autenticado");
            }

            // 4. Insertar pedido
            $stmtInsert = $con->prepare("
                INSERT INTO pedido (id_pedido, mesa_id, mozo_id, hora_inicio, estado_pedido, total)
                VALUES (:id, :mesa_id, :mozo_id, NOW(), 'abierto', 0)
            ");
            
            $stmtInsert->bindParam(':id', $id_pedido);
            $stmtInsert->bindParam(':mesa_id', $mesa_id_db);
            $stmtInsert->bindParam(':mozo_id', $mozo_id);
            $stmtInsert->execute();

            // 5. Actualizar estado de mesa
            $stmtUpdateMesa = $con->prepare("UPDATE mesa SET estado_mesa = 'ocupada' WHERE id_mesa = :id");
            $stmtUpdateMesa->bindParam(':id', $mesa_id_db);
            $stmtUpdateMesa->execute();

            $con->commit();
            return $id_pedido;

        } catch (Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error al crear pedido: " . $e->getMessage());
            return false;
        }
    }

    /* ===========================
       == AGREGAR PLATO A PEDIDO ==
       =========================== */
    
    public static function agregarPlatoAPedidoDB($pedido_id, $plato_id, $cantidad) {
        try {
            $con = Conexion::conectar();
            $con->beginTransaction();
            
            // 1. Verificar que el pedido está abierto
            $stmtPedido = $con->prepare("SELECT estado_pedido FROM pedido WHERE id_pedido = :id");
            $stmtPedido->bindParam(':id', $pedido_id);
            $stmtPedido->execute();
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if (!$pedido || $pedido['estado_pedido'] !== 'abierto') {
                throw new Exception("Pedido no disponible");
            }

            // 2. Obtener precio del plato
            $stmtPlato = $con->prepare("SELECT precio FROM plato WHERE id_plato = :id AND estado = 'activo'");
            $stmtPlato->bindParam(':id', $plato_id);
            $stmtPlato->execute();
            $plato = $stmtPlato->fetch(PDO::FETCH_ASSOC);

            if (!$plato) {
                throw new Exception("Plato no disponible");
            }

            $precio_unit = $plato['precio'];
            $subtotal = $precio_unit * $cantidad;

            // 3. Verificar si el plato ya existe en el detalle
            $stmtCheck = $con->prepare("
                SELECT id_detalle, cantidad FROM detalle_pedido 
                WHERE pedido_id = :pedido_id AND plato_id = :plato_id
            ");
            $stmtCheck->bindParam(':pedido_id', $pedido_id);
            $stmtCheck->bindParam(':plato_id', $plato_id);
            $stmtCheck->execute();
            $detalleExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($detalleExistente) {
                // Actualizar cantidad
                $nuevaCantidad = $detalleExistente['cantidad'] + $cantidad;
                $nuevoSubtotal = $precio_unit * $nuevaCantidad;
                
                $stmtUpdate = $con->prepare("
                    UPDATE detalle_pedido 
                    SET cantidad = :cantidad, subtotal = :subtotal 
                    WHERE id_detalle = :id
                ");
                $stmtUpdate->bindParam(':cantidad', $nuevaCantidad);
                $stmtUpdate->bindParam(':subtotal', $nuevoSubtotal);
                $stmtUpdate->bindParam(':id', $detalleExistente['id_detalle']);
                $stmtUpdate->execute();
            } else {
                // Insertar nuevo detalle
                $id_detalle = self::generarIdDetalleDB($con);
                
                $stmtInsert = $con->prepare("
                    INSERT INTO detalle_pedido (id_detalle, pedido_id, plato_id, cantidad, precio_unit, subtotal)
                    VALUES (:id_detalle, :pedido_id, :plato_id, :cantidad, :precio_unit, :subtotal)
                ");
                $stmtInsert->bindParam(':id_detalle', $id_detalle);
                $stmtInsert->bindParam(':pedido_id', $pedido_id);
                $stmtInsert->bindParam(':plato_id', $plato_id);
                $stmtInsert->bindParam(':cantidad', $cantidad);
                $stmtInsert->bindParam(':precio_unit', $precio_unit);
                $stmtInsert->bindParam(':subtotal', $subtotal);
                $stmtInsert->execute();
            }

            // 4. Actualizar total del pedido
            self::actualizarTotalPedido($con, $pedido_id);

            $con->commit();
            return true;

        } catch (Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error al agregar plato: " . $e->getMessage());
            return false;
        }
    }

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

    /* ===========================
       ====== CERRAR PEDIDO =======
       =========================== */
    
    public static function cerrarPedidoDB($pedido_id) {
        try {
            $con = Conexion::conectar();
            $con->beginTransaction();

            // 1. Obtener mesa_id del pedido
            $stmtPedido = $con->prepare("SELECT mesa_id FROM pedido WHERE id_pedido = :id");
            $stmtPedido->bindParam(':id', $pedido_id);
            $stmtPedido->execute();
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                throw new Exception("Pedido no encontrado");
            }

            // 2. Cerrar pedido
            $stmtClose = $con->prepare("
                UPDATE pedido 
                SET estado_pedido = 'cerrado', hora_cierre = NOW() 
                WHERE id_pedido = :id
            ");
            $stmtClose->bindParam(':id', $pedido_id);
            $stmtClose->execute();

            // 3. Cambiar mesa a limpiando
            $stmtMesa = $con->prepare("UPDATE mesa SET estado_mesa = 'limpiando' WHERE id_mesa = :id");
            $stmtMesa->bindParam(':id', $pedido['mesa_id']);
            $stmtMesa->execute();

            $con->commit();
            return true;

        } catch (Exception $e) {
            if (isset($con) && $con->inTransaction()) {
                $con->rollBack();
            }
            error_log("Error al cerrar pedido: " . $e->getMessage());
            return false;
        }
    }

    /* ===========================
       ====== LIMPIAR MESA ========
       =========================== */
    
    public static function limpiarMesaDB($mesa_numero) {
        try {
            $con = Conexion::conectar();
            
            $stmt = $con->prepare("
                UPDATE mesa 
                SET estado_mesa = 'libre' 
                WHERE numero_mesa = :numero
            ");
            $stmt->bindParam(':numero', $mesa_numero);
            $stmt->execute();
            
            return true;

        } catch (Exception $e) {
            error_log("Error al limpiar mesa: " . $e->getMessage());
            return false;
        }
    }

    /* ===========================
       ==== OBTENER MESA POR NÚMERO ====
       =========================== */
    
    public static function getMesa($numero) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("SELECT * FROM mesa WHERE numero_mesa = :numero");
            $stmt->bindParam(':numero', $numero);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;

            $mesa = new Mesa($data['numero_mesa']);
            $mesa->setEstado($data['estado_mesa']);

            // Buscar pedido abierto
            if ($data['estado_mesa'] === 'ocupada') {
                $stmtPedido = $con->prepare("
                    SELECT id_pedido FROM pedido 
                    WHERE mesa_id = :mesa_id AND estado_pedido = 'abierto'
                    ORDER BY hora_inicio DESC LIMIT 1
                ");
                $stmtPedido->bindParam(':mesa_id', $data['id_mesa']);
                $stmtPedido->execute();
                $pedidoData = $stmtPedido->fetch(PDO::FETCH_ASSOC);
                
                if ($pedidoData) {
                    $mesa->setPedidoId($pedidoData['id_pedido']);
                }
            }

            return $mesa;

        } catch (Exception $e) {
            error_log("Error al obtener mesa: " . $e->getMessage());
            return null;
        }
    }

    /* ===========================
       ====== TODAS LAS MESAS =====
       =========================== */
    
    public static function getTodasLasMesas() {
        try {
            $con = Conexion::conectar();
            $stmt = $con->query("SELECT * FROM mesa ORDER BY numero_mesa");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $mesas = [];
            foreach ($result as $data) {
                $mesa = new Mesa($data['numero_mesa']);
                $mesa->setEstado($data['estado_mesa']);
                
                if ($data['estado_mesa'] === 'ocupada') {
                    $stmtPedido = $con->prepare("
                        SELECT id_pedido FROM pedido 
                        WHERE mesa_id = :mesa_id AND estado_pedido = 'abierto'
                        LIMIT 1
                    ");
                    $stmtPedido->bindParam(':mesa_id', $data['id_mesa']);
                    $stmtPedido->execute();
                    $pedidoData = $stmtPedido->fetch(PDO::FETCH_ASSOC);
                    
                    if ($pedidoData) {
                        $mesa->setPedidoId($pedidoData['id_pedido']);
                    }
                }
                
                $mesas[$data['numero_mesa']] = $mesa;
            }
            
            return $mesas;

        } catch (Exception $e) {
            error_log("Error al obtener mesas: " . $e->getMessage());
            return [];
        }
    }

    /* ===========================
       ===== OBTENER PEDIDO =======
       =========================== */
    
    public static function getPedidoById($pedido_id) {
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

            // Cargar detalles
            $detalles = self::obtenerDetallesPedidoDB($pedido_id);
            foreach ($detalles as $det) {
                $plato = new Plato(
                    $det['plato_id'],
                    $det['plato_nombre'],
                    $det['precio_unit'],
                    'General'
                );
                $pedido->agregarPlato($plato, $det['cantidad']);
            }
            
            // Recalcular total desde detalles
            $pedido->calcularTotal();

            return $pedido;

        } catch (Exception $e) {
            error_log("Error en getPedidoById: " . $e->getMessage());
            return null;
        }
    }

    /* ===========================
       ======= OBTENER PLATOS =====
       =========================== */
    
    public static function getTodosLosPlatos() {
        try {
            $con = Conexion::conectar();
            $stmt = $con->query("SELECT * FROM plato WHERE estado = 'activo' ORDER BY categoria, nombre");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $platos = [];
            foreach ($result as $data) {
                $platos[] = new Plato(
                    $data['id_plato'],
                    $data['nombre'],
                    $data['precio'],
                    $data['categoria']
                );
            }
            
            return $platos;

        } catch (Exception $e) {
            error_log("Error al obtener platos: " . $e->getMessage());
            return [];
        }
    }

    public static function getPlatoById($id) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("SELECT * FROM plato WHERE id_plato = :id AND estado = 'activo'");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) return null;

            return new Plato(
                $data['id_plato'],
                $data['nombre'],
                $data['precio'],
                $data['categoria']
            );

        } catch (Exception $e) {
            error_log("Error al obtener plato: " . $e->getMessage());
            return null;
        }
    }

    /* ===========================
       ======== HISTORIAL =========
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
            error_log("Error al obtener pedidos: " . $e->getMessage());
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
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    public static function obtenerDetallesPedidoDB($id_pedido) {
        try {
            $con = Conexion::conectar();
            $stmt = $con->prepare("
                SELECT dp.*, pl.nombre AS plato_nombre
                FROM detalle_pedido dp
                INNER JOIN plato pl ON dp.plato_id = pl.id_plato
                WHERE dp.pedido_id = :pedido
            ");
            $stmt->bindParam(':pedido', $id_pedido);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }
public static function eliminarPlatoDePedidoDB($pedido_id, $plato_id) {
    try {
        $con = Conexion::conectar();
        $con->beginTransaction();

        // 1️⃣ Eliminar el plato
        $stmt = $con->prepare("DELETE FROM detalle_pedido WHERE pedido_id = :pedido_id AND plato_id = :plato_id");
        $stmt->bindParam(':pedido_id', $pedido_id);
        $stmt->bindParam(':plato_id', $plato_id);
        $stmt->execute();

        // 2️⃣ Recalcular total del pedido
        $stmtTotal = $con->prepare("
            SELECT COALESCE(SUM(subtotal), 0) AS total
            FROM detalle_pedido
            WHERE pedido_id = :pedido
        ");
        $stmtTotal->bindParam(':pedido', $pedido_id);
        $stmtTotal->execute();
        $total = $stmtTotal->fetchColumn();

        // 3️⃣ Actualizar total en pedido
        $stmtUpdate = $con->prepare("UPDATE pedido SET total = :total WHERE id_pedido = :id");
        $stmtUpdate->bindParam(':total', $total);
        $stmtUpdate->bindParam(':id', $pedido_id);
        $stmtUpdate->execute();

        // 4️⃣ Asegurar que el pedido siga "abierto"
        $stmtEstado = $con->prepare("
            UPDATE pedido 
            SET estado_pedido = 'abierto' 
            WHERE id_pedido = :id AND estado_pedido != 'cerrado'
        ");
        $stmtEstado->bindParam(':id', $pedido_id);
        $stmtEstado->execute();

        $con->commit();
        return true;
    } catch (Exception $e) {
        if (isset($con) && $con->inTransaction()) $con->rollBack();
        error_log("Error al eliminar plato del pedido: " . $e->getMessage());
        return false;
    }
}



// 🔵 Eliminar todo el pedido
public static function eliminarPedidoDB($id_pedido) {
    try {
        $con = Conexion::conectar();
        $con->beginTransaction();

        // Buscar mesa
        $stmtPedido = $con->prepare("SELECT mesa_id FROM pedido WHERE id_pedido = :id");
        $stmtPedido->bindParam(':id', $id_pedido);
        $stmtPedido->execute();
        $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            $con->rollBack();
            return false;
        }

        // Borrar detalles del pedido
        $stmt = $con->prepare("DELETE FROM detalle_pedido WHERE pedido_id = :id");
        $stmt->bindParam(':id', $id_pedido);
        $stmt->execute();

        // Borrar pedido
        $stmt2 = $con->prepare("DELETE FROM pedido WHERE id_pedido = :id");
        $stmt2->bindParam(':id', $id_pedido);
        $stmt2->execute();

        // Liberar mesa
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
       ====== FUNCIONES AUXILIARES ======
       =========================== */
    
    private static function generarIdPedidoDB($con) {
        $stmt = $con->query("SELECT COUNT(*) + 1 as siguiente FROM pedido");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return 'PED' . str_pad($resultado['siguiente'], 3, '0', STR_PAD_LEFT);
    }

    private static function generarIdDetalleDB($con) {
        $stmt = $con->query("SELECT COUNT(*) + 1 as siguiente FROM detalle_pedido");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return 'D' . str_pad($res['siguiente'], 3, '0', STR_PAD_LEFT);
    }

    private static function actualizarTotalPedido($con, $pedido_id) {
        $stmt = $con->prepare("
            SELECT SUM(subtotal) as total FROM detalle_pedido WHERE pedido_id = :id
        ");
        $stmt->bindParam(':id', $pedido_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'] ?? 0;

        $stmtUpdate = $con->prepare("UPDATE pedido SET total = :total WHERE id_pedido = :id");
        $stmtUpdate->bindParam(':total', $total);
        $stmtUpdate->bindParam(':id', $pedido_id);
        $stmtUpdate->execute();
    }

    // Métodos de compatibilidad (ya no usan sesión)
    public static function inicializarMesas() {
        // Ya no es necesario, todo viene de BD
    }

    public static function inicializarMenu() {
        // Ya no es necesario, todo viene de BD
    }
}
?>