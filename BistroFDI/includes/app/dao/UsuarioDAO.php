<?php
declare(strict_types=1);

require_once __DIR__ . '/../dto/UsuarioDTO.php';

class UsuarioDAO {
    public function __construct(private mysqli $conn) {}

    public function findById(int $id): ?UsuarioDTO {
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (findById usuarios): " . $this->conn->error); }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (findById usuarios): " . $stmt->error); }

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }
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
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                WHERE nombre_usuario = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (findByNombreUsuario usuarios): " . $this->conn->error); }

        $stmt->bind_param("s", $nombreUsuario);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (findByNombreUsuario usuarios): " . $stmt->error); }

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) { return null;}

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

    public function findByEmail(string $email): ?UsuarioDTO {
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                WHERE email = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (findByEmail usuarios): " . $this->conn->error); }

        $stmt->bind_param("s", $email);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (findByEmail usuarios): " . $stmt->error); }

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) {return null;}

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

    public function insert(string $nombreUsuario, string $email, string $passwordHash, string $nombre, string $apellidos, ?string $avatar): int {
        $sql = "INSERT INTO usuarios (nombre_usuario, email, password, nombre, apellidos, avatar)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (insert usuarios): " . $this->conn->error); }

        $stmt->bind_param("ssssss", $nombreUsuario, $email, $passwordHash, $nombre, $apellidos, $avatar);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (insert usuarios): " . $stmt->error); }

        $id = (int)$this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    public function updatePerfil(int $id, string $nombreUsuario, string $email, string $nombre, string $apellidos, ?string $avatar): bool {
        $sql = "UPDATE usuarios
                SET nombre_usuario = ?, email = ?, nombre = ?, apellidos = ?, avatar = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (updatePerfil usuarios): " . $this->conn->error); }

        $stmt->bind_param("sssssi", $nombreUsuario, $email, $nombre, $apellidos, $avatar, $id);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (updatePerfil usuarios): " . $stmt->error); }

        $stmt->close();
        return true;
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool {
        $sql = "UPDATE usuarios
                SET password = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (updatePasswordHash usuarios): " . $this->conn->error); }

        $stmt->bind_param("si", $passwordHash, $id);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (updatePasswordHash usuarios): " . $stmt->error); }

        $stmt->close();
        return true;
    }

    public function findAll(): array {
        $sql = "SELECT id, nombre_usuario, email, password, nombre, apellidos, avatar
                FROM usuarios
                ORDER BY id ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (findAll usuarios): " . $this->conn->error); }

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (findAll usuarios): " . $stmt->error); }

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

    public function tienePedidosAsociados(int $id): bool {
        $sql = "SELECT COUNT(*) AS total
                FROM pedidos
                WHERE id_cliente = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (tienePedidosAsociados usuarios): " . $this->conn->error); }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {throw new RuntimeException("Error execute (tienePedidosAsociados usuarios): " . $stmt->error); }

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        $total = isset($row['total']) ? (int)$row['total'] : 0;
        return $total > 0;
    }

    public function deleteById(int $id): bool {
        $sql = "DELETE FROM usuarios
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (deleteById usuarios): " . $this->conn->error); }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) { throw new RuntimeException("Error execute (deleteById usuarios): " . $stmt->error); }

        $stmt->close();
        return true;
    }
}