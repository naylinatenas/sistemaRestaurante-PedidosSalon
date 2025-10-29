<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // evitar que warnings rompan JSON
session_start();

require_once __DIR__ . '/../modelo/PlatoDAO.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado']);
    exit();
}

$dao = new PlatoDAO();

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // Crear / editar (ya funcionaban antes)
        $accion = $_POST['accion'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'Entrada');
        $precio = floatval($_POST['precio'] ?? 0);
        $estado = $_POST['estado'] ?? 'activo';

        if ($precio <= 0 || $nombre === '') {
            throw new Exception('Nombre y precio válidos son obligatorios.');
        }

        if ($accion === 'crear') {
            $id_plato = trim($_POST['id_plato'] ?? '');
            $nuevoId = $dao->crearPlato($id_plato, $nombre, $categoria, $precio, $estado);
            echo json_encode(['status' => 'success', 'message' => "Plato $nuevoId creado.", 'id' => $nuevoId]);
            exit();
        }

        if ($accion === 'editar') {
            $id_original = trim($_POST['id_plato_original'] ?? '');
            $id_nuevo = trim($_POST['id_plato'] ?? $id_original);
            if ($id_original === '') throw new Exception('ID original no válido.');
            $dao->editarPlato($id_original, $id_nuevo, $nombre, $categoria, $precio, $estado);
            echo json_encode(['status' => 'success', 'message' => "Plato $id_nuevo editado."]);
            exit();
        }

        echo json_encode(['status' => 'error', 'message' => 'Acción POST no válida.']);
        exit();
    }

    // ELIMINAR vía GET (AJAX fetch)
    if ($method === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
        $id = trim($_GET['id'] ?? '');
        if ($id === '') {
            throw new Exception('ID no válido.');
        }

        $ok = $dao->eliminarPlato($id); // arrojará o retornará true/false
        if ($ok) {
            echo json_encode(['status' => 'success', 'message' => "Plato $id eliminado o desactivado."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "No se pudo eliminar el plato $id."]);
        }
        exit();
    }

    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit();

} catch (Exception $e) {
    error_log("[platoControlador] " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
