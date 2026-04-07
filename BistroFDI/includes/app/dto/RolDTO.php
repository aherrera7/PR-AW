<?php
declare(strict_types=1);

class RolDTO implements JsonSerializable {
    // Atributos
    public function __construct(
        private int $idRol,
        private string $nombreRol
    ) {}

    // Getters
    public function getId(): int { return $this->idRol; }
    public function getNombre(): string { return $this->nombreRol; }
    public function jsonSerialize(): mixed { return ['id_rol' => $this->idRol, 'nombre_rol' => $this->nombreRol];}
}