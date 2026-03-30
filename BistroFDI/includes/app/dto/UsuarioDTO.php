<?php
declare(strict_types=1);

require_once __DIR__ . '/RolDTO.php';

class UsuarioDTO implements JsonSerializable
{
    // Atributos
    public function __construct(
        private ?int $id,
        private string $nombreUsuario,
        private string $passwordHash,
        private string $nombre,
        private string $email = '',
        private string $apellidos = '',
        private ?string $avatar = null,
        private array $roles = []
    ) {}

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNombreUsuario(): string { return $this->nombreUsuario; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getNombre(): string { return $this->nombre; }
    public function getEmail(): string { return $this->email; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getAvatar(): ?string { return $this->avatar; }

    public function getRoles(): array { return $this->roles; }
    public function setRoles(array $roles): void { $this->roles = $roles; }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nombreUsuario' => $this->nombreUsuario,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'apellidos' => $this->apellidos,
            'avatar' => $this->avatar,
            'roles' => $this->roles,
        ];
    }
}