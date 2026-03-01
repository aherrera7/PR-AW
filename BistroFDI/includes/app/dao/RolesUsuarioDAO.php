<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Aplicacion.php';

class RolesUsuarioDAO
{
    public function insert(int $idUsuario, int $idRol): bool
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "INSERT INTO RolesUsuario(usuario, rol) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idUsuario, $idRol);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}