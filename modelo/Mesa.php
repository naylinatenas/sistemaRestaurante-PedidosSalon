<?php
class Mesa {
    private $numero;
    private $estado; // 'libre', 'ocupada', 'limpiando'
    private $pedido_id;
    
    public function __construct($numero, $estado = 'libre', $pedido_id = null) {
        $this->numero = $numero;
        $this->estado = $estado;
        $this->pedido_id = $pedido_id;
    }
    
    public function getNumero() {
        return $this->numero;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function setEstado($estado) {
        $this->estado = $estado;
    }
    
    public function getPedidoId() {
        return $this->pedido_id;
    }
    
    public function setPedidoId($pedido_id) {
        $this->pedido_id = $pedido_id;
    }
    
    public function estaLibre() {
        return $this->estado === 'libre';
    }
    
    public function estaOcupada() {
        return $this->estado === 'ocupada';
    }
}
?>
