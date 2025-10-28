<?php
class Pedido {
    private $id;
    private $mesa_numero;
    private $items; // Array de items del pedido
    private $total;
    private $estado; // 'abierto', 'cerrado'
    private $fecha;
    
    public function __construct($id, $mesa_numero) {
        $this->id = $id;
        $this->mesa_numero = $mesa_numero;
        $this->items = [];
        $this->total = 0;
        $this->estado = 'abierto';
        $this->fecha = date('Y-m-d H:i:s');
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getMesaNumero() {
        return $this->mesa_numero;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function agregarItem($plato, $cantidad) {
        // Verificar si el plato ya existe en el pedido
        foreach ($this->items as &$item) {
            if ($item['plato']->getId() === $plato->getId()) {
                $item['cantidad'] += $cantidad;
                $this->calcularTotal();
                return;
            }
        }
        
        // Si no existe, agregar nuevo item
        $this->items[] = [
            'plato' => $plato,
            'cantidad' => $cantidad
        ];
        $this->calcularTotal();
    }
    
    public function calcularTotal() {
        $this->total = 0;
        foreach ($this->items as $item) {
            $this->total += $item['plato']->getPrecio() * $item['cantidad'];
        }
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function cerrar() {
        $this->estado = 'cerrado';
    }
    
    public function getFecha() {
        return $this->fecha;
    }

    // ✅ Añadir este método para el dashboard
    public function getDetalles() {
        return $this->items;
    }
}
?>
