<?php
declare(strict_types=1);

class OfertaProductoDTO {
    private int $idOferta;
    private int $idProducto;
    private int $cantidad;

    public function __construct(int $idOferta, int $idProducto, int $cantidad)
    {
        $this->idOferta = $idOferta;
        $this->idProducto = $idProducto;
        $this->cantidad = $cantidad;
    }

    public function getIdOferta(): int
    {
        return $this->idOferta;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }
}