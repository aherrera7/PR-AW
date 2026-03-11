<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Aplicacion.php';
require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/../dao/RolDAO.php';

class UsuarioSA
{
    private UsuarioDAO $usuarioDAO;
    private RolDAO $rolDAO;

    public function __construct(?UsuarioDAO $usuarioDAO = null, ?RolDAO $rolDAO = null)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $this->usuarioDAO = $usuarioDAO ?? new UsuarioDAO($conn);
        $this->rolDAO = $rolDAO ?? new RolDAO($conn);
    }

    public function login(string $nombreUsuario, string $passwordPlano): ?UsuarioDTO {
        $u = $this->usuarioDAO->findByNombreUsuario($nombreUsuario);
        if (!$u) return null;

        $guardada = (string)$u->getPasswordHash();

        $info = password_get_info($guardada);
        $esHash = ($info['algo'] ?? 0) !== 0;

        if ($esHash) {
            if (!password_verify($passwordPlano, $guardada)) return null;

            if (password_needs_rehash($guardada, PASSWORD_DEFAULT)) {
                $nuevoHash = password_hash($passwordPlano, PASSWORD_DEFAULT);
                $this->usuarioDAO->updatePasswordHash($u->getId(), $nuevoHash);
            }
        } else {
            if (!hash_equals($guardada, $passwordPlano)) return null;

            $nuevoHash = password_hash($passwordPlano, PASSWORD_DEFAULT);
            $this->usuarioDAO->updatePasswordHash($u->getId(), $nuevoHash);
        }

        $u = $this->usuarioDAO->findById($u->getId()) ?? $u;

        $u->setRoles($this->rolDAO->findRolesByUsuarioId($u->getId()));
        return $u;
    }

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

    public function getById(int $id): ?UsuarioDTO
    {
        $u = $this->usuarioDAO->findById($id);
        if (!$u) return null;

        $u->setRoles($this->rolDAO->findRolesByUsuarioId($id));
        return $u;
    }

    public function actualizarPerfil(
        int $id,
        string $nombreUsuario,
        string $email,
        string $nombre,
        string $apellidos,
        ?string $avatar
    ): ?UsuarioDTO {
        $exist = $this->usuarioDAO->findByNombreUsuario($nombreUsuario);
        if ($exist && $exist->getId() !== $id) {
            return null;
        }

        $ok = $this->usuarioDAO->updatePerfil($id, $nombreUsuario, $email, $nombre, $apellidos, $avatar);
        if (!$ok) return null;

        return $this->getById($id);
    }

    public function listarUsuarios(): array {
        $usuarios = $this->usuarioDAO->findAll();
        foreach ($usuarios as $u) {
            $u->setRoles($this->rolDAO->findRolesByUsuarioId($u->getId()));
        }
        return $usuarios;
    }

    public function cambiarRolUsuario(int $idUsuario, string $nombreRol): bool {
        $u = $this->usuarioDAO->findById($idUsuario);
        if (!$u) return false;

        $idRol = $this->rolDAO->getOrCreateRolId($nombreRol);
        $this->rolDAO->setRolUnicoUsuario($idUsuario, $idRol);

        return true;
    }

    public function borrarUsuario(int $idUsuario): bool {
        return $this->usuarioDAO->deleteById($idUsuario);
    }
}