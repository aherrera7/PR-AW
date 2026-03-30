<?php
declare(strict_types=1);
// Representa una fila de 'pedidos_productos' 

class PedidoProductoDTO{

    // Atributos
    public function __construct(
        private int $idPedido,
        private int $idProducto,
        private int $cantidad,
        private float $precioHistorico
    ) {}

    // Getters
    public function getIdPedido(): int { return $this->idPedido; }
    public function getIdProducto(): int { return $this->idProducto; }
    public function getCantidad(): int { return $this->cantidad; }
    public function getPrecioHistorico(): float { return $this->precioHistorico; }
    public function getSubtotal(): float {
        return round($this->cantidad * $this->precioHistorico, 2);
    }
}