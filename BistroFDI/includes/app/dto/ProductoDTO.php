<?php
declare(strict_types=1);

class ProductoDTO
{
    /**
     * @param string[] $imagenes
     */
    public function __construct(
        private ?int $id,
        private int $idCategoria,
        private string $nombre,
        private ?string $descripcion,
        private float $precioBase,
        private int $iva,
        private bool $disponible,
        private bool $ofertado,
        private array $imagenes = []
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getIdCategoria(): int { return $this->idCategoria; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getPrecioBase(): float { return $this->precioBase; }
    public function getIva(): int { return $this->iva; }
    public function isDisponible(): bool { return $this->disponible; }
    public function isOfertado(): bool { return $this->ofertado; }

    /** @return string[] */
    public function getImagenes(): array { return $this->imagenes; }

    public function getPrecioFinal(): float
    {
        return round($this->precioBase * (1.0 + ($this->iva / 100.0)), 2);
    }
}