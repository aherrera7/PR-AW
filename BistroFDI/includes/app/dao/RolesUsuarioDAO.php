<?php
declare(strict_types=1);

class RolesUsuarioDAO
{
    public function __construct(private mysqli $conn) {}

    public function insert(int $idUsuario, int $idRol): bool
    {
        $sql = "INSERT INTO roles_usuarios(id_usuario, id_rol) VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (insert roles_usuarios): " . $this->conn->error); }

        $stmt->bind_param("ii", $idUsuario, $idRol);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (insert roles_usuarios): " . $stmt->error); }

        $stmt->close();
        return true;
    }

    public function deleteAllRolesFromUsuario(int $idUsuario): bool {
        $sql = "DELETE FROM roles_usuarios WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (deleteAllRolesFromUsuario): " . $this->conn->error); }

        $stmt->bind_param("i", $idUsuario);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (deleteAllRolesFromUsuario): " . $stmt->error); }

        $stmt->close();
        return true;
    }
}