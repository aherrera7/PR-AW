<?php
declare(strict_types=1);

class OfertaDTO {
    public function __construct(
        private int $id,
        private string $nombre,
        private string $descripcion,
        private string $fechaInicio,
        private string $fechaFin,
        private float $descuento,
        private bool $activa
    ) {
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }

    public function getFechaInicio(): string {
        return $this->fechaInicio;
    }

    public function getFechaFin(): string {
        return $this->fechaFin;
    }

    public function getDescuento(): float {
        return $this->descuento;
    }

    public function isActiva(): bool {
        return $this->activa;
    }
}