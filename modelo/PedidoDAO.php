<?php
require_once __DIR__ . '/Mesa.php';
require_once __DIR__ . '/Plato.php';
require_once __DIR__ . '/Pedido.php';

class PedidoDAO {
    
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
        return $_SESSION['mesas'][$numero] ?? null;
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
        $mesa->setEstado('ocupada');
        $mesa->setPedidoId($id);
        
        return $pedido;
    }
    
    public static function getPedidoById($id) {
        return $_SESSION['pedidos'][$id] ?? null;
    }
    
    public static function cerrarPedido($pedido_id) {
        $pedido = self::getPedidoById($pedido_id);
        if ($pedido) {
            $pedido->cerrar();
            $mesa = self::getMesa($pedido->getMesaNumero());
            $mesa->setEstado('limpiando');
        }
    }
    
    public static function limpiarMesa($mesa_numero) {
        $mesa = self::getMesa($mesa_numero);
        if ($mesa) {
            $mesa->setEstado('libre');
            $mesa->setPedidoId(null);
        }
    }
}
?>
