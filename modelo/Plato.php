<?php
class Plato {
    private $id;
    private $nombre;
    private $precio;
    private $categoria;
    
    public function __construct($id, $nombre, $precio, $categoria) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->categoria = $categoria;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNombre() {
        return $this->nombre;
    }
    
    public function getPrecio() {
        return $this->precio;
    }
    
    public function getCategoria() {
        return $this->categoria;
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'categoria' => $this->categoria
        ];
    }
}
?>