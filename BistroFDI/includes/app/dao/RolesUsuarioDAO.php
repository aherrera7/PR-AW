<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Aplicacion.php';

class RolesUsuarioDAO
{
    public function insert(int $idUsuario, int $idRol): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "INSERT INTO roles_usuarios(id_usuario, id_rol) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idUsuario, $idRol);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function deleteAllRolesFromUsuario(int $idUsuario): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "DELETE FROM roles_usuarios WHERE id_usuario = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}