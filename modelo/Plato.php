<?php
class Plato {
    public $id;
    public $nombre;
    public $categoria;
    public $precio;
    public $veces;

    public function __construct($id=null,$nombre=null,$categoria=null,$precio=0,$veces=0){
        $this->id=$id; $this->nombre=$nombre; $this->categoria=$categoria; $this->precio=$precio; $this->veces=$veces;
    }
}
