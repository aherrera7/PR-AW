<?php
declare(strict_types=1);

class CategoriaDTO
{
    public function __construct(
        private ?int $id,
        private string $nombre,
        private ?string $descripcion,
        private ?string $imagen
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getImagen(): ?string { return $this->imagen; }
}