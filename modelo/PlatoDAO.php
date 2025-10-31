<?php
require_once __DIR__ . '/../conexion/Conexion.php';

class PlatoDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // ===============================
    // 🔹 Generar ID incremental (P001, P002, etc.)
    // ===============================
    private function generarProximoId() {
        $stmt = $this->pdo->query("SELECT id_plato FROM plato ORDER BY id_plato DESC LIMIT 1");
        $ultimoId = $stmt->fetchColumn();

        if ($ultimoId) {
            $numero = intval(substr($ultimoId, 1)); 
            $nuevoNumero = $numero + 1;
        } else {
            $nuevoNumero = 1;
        }

        return 'P' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);
    }

    // ===============================
    // 🔹 Crear nuevo plato
    // ===============================
    public function crearPlato($id_plato, $nombre, $categoria, $precio, $estado = 'activo') {
        if (empty($id_plato)) {
            $id_plato = $this->generarProximoId();
        }

        $id_plato = strtoupper(trim($id_plato));

        // Verificar que el ID no exista
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM plato WHERE id_plato = ?");
        $check->execute([$id_plato]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("El ID '$id_plato' ya existe.");
        }

        $sql = "INSERT INTO plato (id_plato, nombre, categoria, precio, estado)
                VALUES (:id_plato, :nombre, :categoria, :precio, :estado)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_plato' => $id_plato,
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':precio' => $precio,
            ':estado' => $estado
        ]);

        return $id_plato;
    }

    // ===============================
    // 🔹 Editar plato (con posibilidad de cambiar ID)
    // ===============================
    public function editarPlato($id_plato_original, $id_plato_nuevo, $nombre, $categoria, $precio, $estado) {
        $id_plato_nuevo = strtoupper(trim($id_plato_nuevo));

        // Si no se cambia el ID, solo actualiza datos
        if ($id_plato_original === $id_plato_nuevo) {
            $sql = "UPDATE plato 
                    SET nombre = :nombre,
                        categoria = :categoria,
                        precio = :precio,
                        estado = :estado
                    WHERE id_plato = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre' => $nombre,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':estado' => $estado,
                ':id' => $id_plato_original
            ]);
        }

        // Si cambia el ID, validar que el nuevo no exista
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM plato WHERE id_plato = ?");
        $check->execute([$id_plato_nuevo]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("El ID '$id_plato_nuevo' ya existe.");
        }

        $this->pdo->beginTransaction();

        try {
            // Actualizar referencias en detalle_pedido
            $stmt = $this->pdo->prepare("UPDATE detalle_pedido SET plato_id = ? WHERE plato_id = ?");
            $stmt->execute([$id_plato_nuevo, $id_plato_original]);

            // Insertar el nuevo registro
            $stmt = $this->pdo->prepare("
                INSERT INTO plato (id_plato, nombre, categoria, precio, estado)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_plato_nuevo, $nombre, $categoria, $precio, $estado]);

            // Eliminar el anterior
            $stmt = $this->pdo->prepare("DELETE FROM plato WHERE id_plato = ?");
            $stmt->execute([$id_plato_original]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al cambiar el ID: " . $e->getMessage());
        }
    }

    // ===============================
    // 🔹 Eliminar (o desactivar si está en uso)
    // ===============================
    public function eliminarPlato($id_plato) {
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM detalle_pedido WHERE plato_id = ?");
        $check->execute([$id_plato]);
        $enUso = $check->fetchColumn();

        if ($enUso > 0) {
            // Si el plato ya está en pedidos, marcar como inactivo
            $stmt = $this->pdo->prepare("UPDATE plato SET estado = 'inactivo' WHERE id_plato = ?");
            return $stmt->execute([$id_plato]);
        } else {
            // Si no está en uso, eliminarlo
            $stmt = $this->pdo->prepare("DELETE FROM plato WHERE id_plato = ?");
            return $stmt->execute([$id_plato]);
        }
    }

    // ===============================
    // 🔹 Listar todos los platos
    // ===============================
    public function listarPlatos() {
        $stmt = $this->pdo->query("SELECT * FROM plato ORDER BY categoria, nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
