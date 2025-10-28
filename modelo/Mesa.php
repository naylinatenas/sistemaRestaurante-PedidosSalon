<?php
class Mesa {
    public $id;
    public $numero;
    public $estado; // libre, ocupada, limpiando

    public function __construct($id=null,$numero=null,$estado='libre'){
        $this->id=$id; $this->numero=$numero; $this->estado=$estado;
    }
}
