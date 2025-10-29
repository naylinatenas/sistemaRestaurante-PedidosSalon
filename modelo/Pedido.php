<?php
class Pedido {
    private $id;
    private $mesa_numero;
    private $items; // Array con los platos y sus cantidades
    private $total;
    private $estado; // 'abierto', 'cerrado'
    private $fecha;

    public function __construct($id, $mesa_numero) {
        $this->id = $id;
        $this->mesa_numero = $mesa_numero;
        $this->items = [];
        $this->total = 0.00;
        $this->estado = 'abierto';
        $this->fecha = date('Y-m-d H:i:s');
    }

    /* ===========================
       ==== GETTERS / SETTERS ====
       =========================== */

    public function getId() {
        return $this->id;
    }

    public function getMesaNumero() {
        return $this->mesa_numero;
    }

    public function getItems() {
        return $this->items;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setTotal($total) {
        $this->total = (float)$total;
    }

    /* ===========================
       ====== FUNCIONES ==========
       =========================== */

    public function agregarItem($plato, $cantidad) {
        // Si el plato ya existe, aumentar cantidad
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

    // ✅ Versión compatible con PedidoDAO
    public function agregarPlato($plato, $cantidad) {
        $this->agregarItem($plato, $cantidad);
    }

    public function calcularTotal() {
        $this->total = 0.00;
        foreach ($this->items as $item) {
            $this->total += $item['plato']->getPrecio() * $item['cantidad'];
        }
    }

    public function cerrar() {
        $this->estado = 'cerrado';
    }

    // ✅ Este método lo usa el PedidoDAO al guardar detalles
    public function getDetalles() {
        return $this->items;
    }

    // ✅ Método para compatibilidad visual en el dashboard
    public function toArray() {
        $detalles = [];
        foreach ($this->items as $item) {
            $detalles[] = [
                'plato' => $item['plato']->getNombre(),
                'cantidad' => $item['cantidad'],
                'precio_unit' => $item['plato']->getPrecio(),
                'subtotal' => $item['plato']->getPrecio() * $item['cantidad']
            ];
        }

        return [
            'id' => $this->id,
            'mesa_numero' => $this->mesa_numero,
            'estado' => $this->estado,
            'total' => $this->total,
            'fecha' => $this->fecha,
            'detalles' => $detalles
        ];
    }
}
?>
