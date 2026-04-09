<?php
declare(strict_types=1);
// Representa una fila de la tabla 'pedidos'

class PedidoDTO{
    // Atributos
    public function __construct(
        private ?int $id,
        private int $numeroPedido,
        private ?int $idCliente,
        private ?int $idCocinero,
        private ?int $idOferta, 
        private string $fechaHora,
        private string $estado,
        private string $tipo,
        private float $subtotal,
        private float $descuento,
        private float $total
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNumeroPedido(): int { return $this->numeroPedido; }
    public function getIdCliente(): ?int { return $this->idCliente; }
    public function getIdOferta(): ?int { return $this->idOferta; }
    public function getFechaHora(): string { return $this->fechaHora; }
    public function getEstado(): string { return $this->estado; }
    public function getTipo(): string { return $this->tipo; }
    public function getSubtotal(): float { return $this->subtotal; }
    public function getDescuento(): float { return $this->descuento; }
    public function getTotal(): float { return $this->total; }
    public function getIdCocinero(): ?int { return $this->idCocinero; }




}