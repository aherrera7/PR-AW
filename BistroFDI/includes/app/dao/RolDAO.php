<?php
declare(strict_types=1);

require_once __DIR__ . '/../dto/RolDTO.php';

class RolDAO
{
    public function __construct(private mysqli $conn) {}

    public function findRolesByUsuarioId(int $idUsuario): array {
        $sql = "SELECT r.id_rol, r.nombre_rol
                FROM roles r
                INNER JOIN roles_usuarios ru ON r.id_rol = ru.id_rol
                WHERE ru.id_usuario = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (findRolesByUsuarioId): " . $this->conn->error); }

        $stmt->bind_param("i", $idUsuario);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (findRolesByUsuarioId): " . $stmt->error); }
        $res = $stmt->get_result();
        $roles = [];

        while ($row = $res->fetch_assoc()) {
            $roles[] = new RolDTO(
                (int)$row['id_rol'],
                (string)$row['nombre_rol']
            );
        }
        $stmt->close();
        return $roles;
    }

    public function getOrCreateRolId(string $nombreRol): int {
        $sql = "SELECT id_rol FROM roles WHERE nombre_rol = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (getOrCreateRolId select): " . $this->conn->error); }

        $stmt->bind_param("s", $nombreRol);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (getOrCreateRolId select): " . $stmt->error); }

        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) { return (int)$row['id_rol']; }

        $sql2 = "INSERT INTO roles(nombre_rol) VALUES (?)";
        $stmt2 = $this->conn->prepare($sql2);
        if (!$stmt2) { throw new RuntimeException("Error prepare (getOrCreateRolId insert): " . $this->conn->error); }

        $stmt2->bind_param("s", $nombreRol);
        if (!$stmt2->execute()) { throw new RuntimeException("Error execute (getOrCreateRolId insert): " . $stmt2->error); }

        $id = (int)$this->conn->insert_id;
        $stmt2->close();
        return $id;
    }

    public function assignRolToUsuario(int $idUsuario, int $idRol): void {
        $sql = "INSERT IGNORE INTO roles_usuarios(id_usuario, id_rol) VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException("Error prepare (assignRolToUsuario): " . $this->conn->error); }

        $stmt->bind_param("ii", $idUsuario, $idRol);
        if (!$stmt->execute()) { throw new RuntimeException("Error execute (assignRolToUsuario): " . $stmt->error); }
        $stmt->close();
    }

    public function setRolUnicoUsuario(int $idUsuario, int $idRol): void {
        $sqlDel = "DELETE FROM roles_usuarios WHERE id_usuario = ?";

        $stmtDel = $this->conn->prepare($sqlDel);
        if (!$stmtDel) { throw new RuntimeException("Error prepare (setRolUnicoUsuario delete): " . $this->conn->error); }

        $stmtDel->bind_param("i", $idUsuario);
        if (!$stmtDel->execute()) { throw new RuntimeException("Error execute (setRolUnicoUsuario delete): " . $stmtDel->error); }

        $stmtDel->close();
        $sqlIns = "INSERT INTO roles_usuarios(id_usuario, id_rol) VALUES (?, ?)";
        $stmtIns = $this->conn->prepare($sqlIns);
        if (!$stmtIns) { throw new RuntimeException("Error prepare (setRolUnicoUsuario insert): " . $this->conn->error); }

        $stmtIns->bind_param("ii", $idUsuario, $idRol);
        if (!$stmtIns->execute()) { throw new RuntimeException("Error execute (setRolUnicoUsuario insert): " . $stmtIns->error); }

        $stmtIns->close();
    }
}