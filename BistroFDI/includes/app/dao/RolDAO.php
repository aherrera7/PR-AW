<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Aplicacion.php';
require_once __DIR__ . '/../dto/RolDTO.php';

class RolDAO
{
    /** @return RolDTO[] */
    public function findRolesByUsuarioId(int $idUsuario): array
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT r.id_rol, r.nombre_rol
                FROM roles r
                INNER JOIN roles_usuarios ru ON r.id_rol = ru.id_rol
                WHERE ru.id_usuario = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        $roles = [];
        while ($row = $res->fetch_assoc()) {
            $roles[] = new RolDTO((int)$row['id_rol'], $row['nombre_rol']);
        }

        $stmt->close();
        return $roles;
    }

    public function getOrCreateRolId(string $nombreRol): int
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        // 1) buscar
        $sql = "SELECT id_rol FROM roles WHERE nombre_rol = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombreRol);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) return (int)$row['id_rol'];

        // 2) crear si no existe
        $sql2 = "INSERT INTO roles(nombre_rol) VALUES (?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $nombreRol);
        $stmt2->execute();
        $id = (int)$conn->insert_id;
        $stmt2->close();

        return $id;
    }

    public function assignRolToUsuario(int $idUsuario, int $idRol): void
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        // evitar duplicados
        $sql = "INSERT IGNORE INTO roles_usuarios(id_usuario, id_rol) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idUsuario, $idRol);
        $stmt->execute();
        $stmt->close();
    }
}