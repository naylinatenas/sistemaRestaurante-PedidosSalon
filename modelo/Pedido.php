<?php
class Pedido {
    public $id;
    public $mesa_id;
    public $mozo;
    public $hora_inicio;
    public $hora_cierre;
    public $estado; // abierto, cerrado
    public $total;
    public $detalles; // array de ['plato_id','cantidad','subtotal']

    public function __construct(){ $this->detalles = []; $this->total = 0; }
}
