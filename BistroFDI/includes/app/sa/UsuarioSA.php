<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/../dao/RolDAO.php';

class UsuarioSA
{
    public function __construct(
        private UsuarioDAO $usuarioDAO = new UsuarioDAO(),
        private RolDAO $rolDAO = new RolDAO()
    ) {}

    public function login(string $nombreUsuario, string $passwordPlano): ?UsuarioDTO
    {
        $u = $this->usuarioDAO->findByNombreUsuario($nombreUsuario);
        if (!$u) return null;

        if (!password_verify($passwordPlano, $u->getPasswordHash())) return null;

        $u->setRoles($this->rolDAO->findRolesByUsuarioId($u->getId()));
        return $u;
    }

    // NUEVO: registra cliente guardando avatar
    public function registrarClienteConAvatar(
        string $nombreUsuario,
        string $email,
        string $passwordPlano,
        string $nombre,
        string $apellidos,
        ?string $avatar
    ): ?UsuarioDTO {
        if ($this->usuarioDAO->findByNombreUsuario($nombreUsuario)) return null;

        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

        $id = $this->usuarioDAO->insert($nombreUsuario, $email, $hash, $nombre, $apellidos, $avatar);

        $idRolCliente = $this->rolDAO->getOrCreateRolId('cliente');
        $this->rolDAO->assignRolToUsuario($id, $idRolCliente);

        $u = $this->usuarioDAO->findById($id);
        if (!$u) return null;

        $u->setRoles($this->rolDAO->findRolesByUsuarioId($id));
        return $u;
    }

    public function tieneRol(UsuarioDTO $usuario, string $nombreRol): bool
    {
        foreach ($usuario->getRoles() as $rol) {
            if ($rol->getNombre() === $nombreRol) return true;
        }
        return false;
    }

    public function getById(int $id): ?UsuarioDTO{
        $u = $this->usuarioDAO->findById($id);
        if (!$u) return null;

        $u->setRoles($this->rolDAO->findRolesByUsuarioId($id));
        return $u;
    }

    public function actualizarPerfil(int $id, string $nombreUsuario, string $email, string $nombre, string $apellidos, ?string $avatar): ?UsuarioDTO{
        // comprobar duplicado de nombre_usuario si cambia
        $exist = $this->usuarioDAO->findByNombreUsuario($nombreUsuario);
        if ($exist && $exist->getId() !== $id) {
            return null; // nombre ocupado
        }

        $ok = $this->usuarioDAO->updatePerfil($id, $nombreUsuario, $email, $nombre, $apellidos, $avatar);
        if (!$ok) return null;

        return $this->getById($id);
    }
}