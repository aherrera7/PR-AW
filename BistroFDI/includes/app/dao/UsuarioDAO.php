<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Aplicacion.php';
require_once __DIR__ . '/../dto/UsuarioDTO.php';

class UsuarioDAO {
    public function findById(int $id): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) return null;
        return new UsuarioDTO(
            (int)$row['id'],
            (string)$row['nombre_usuario'],
            (string)$row['password'],
            (string)$row['nombre'],
            (string)$row['email'],
            (string)$row['apellidos'],
            $row['avatar'] !== null ? (string)$row['avatar'] : null,
            []
        );
    }

    public function findByNombreUsuario(string $nombreUsuario): ?UsuarioDTO {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                WHERE nombre_usuario = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombreUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        return new UsuarioDTO(
            (int)$row['id'],
            (string)$row['nombre_usuario'],
            (string)$row['password'],
            (string)$row['nombre'],
            (string)$row['email'],
            (string)$row['apellidos'],
            $row['avatar'] !== null ? (string)$row['avatar'] : null,
            []
        );
    }

    public function insert(string $nombreUsuario, string $email, string $passwordHash, string $nombre, string $apellidos, ?string $avatar): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "INSERT INTO usuarios(nombre_usuario, email, password, nombre, apellidos, avatar)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nombreUsuario, $email, $passwordHash, $nombre, $apellidos, $avatar);
        $stmt->execute();
        $id = (int)$conn->insert_id;
        $stmt->close();

        return $id;
    }

    public function updatePerfil(int $id, string $nombreUsuario, string $email, string $nombre, string $apellidos, ?string $avatar): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "UPDATE usuarios
                SET nombre_usuario = ?, email = ?, nombre = ?, apellidos = ?, avatar = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nombreUsuario, $email, $nombre, $apellidos, $avatar, $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "UPDATE usuarios SET password = ? WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $passwordHash, $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function findAll(): array {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                ORDER BY id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();

        $usuarios = [];
        while ($row = $res->fetch_assoc()) {
            $usuarios[] = new UsuarioDTO(
                (int)$row['id'],
                (string)$row['nombre_usuario'],
                (string)$row['password'],
                (string)$row['nombre'],
                (string)$row['email'],
                (string)$row['apellidos'],
                $row['avatar'] !== null ? (string)$row['avatar'] : null,
                []
            );
    }

    $stmt->close();
    return $usuarios;
}

    public function deleteById(int $id): bool {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "DELETE FROM usuarios WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}