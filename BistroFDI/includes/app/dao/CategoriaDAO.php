<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dto/CategoriaDTO.php';

class CategoriaDAO
{
    public function __construct(private mysqli $conn) {}

    /** @return CategoriaDTO[] */
    public function findAll(): array
    {
        $sql = "SELECT id, nombre, descripcion, imagen
                FROM categorias
                ORDER BY nombre ASC";

        $rs = $this->conn->query($sql);
        if (!$rs) {
            throw new RuntimeException("Error BD (findAll categorias): " . $this->conn->error);
        }

        $res = [];
        while ($row = $rs->fetch_assoc()) {
            $res[] = new CategoriaDTO(
                (int)$row['id'],
                (string)$row['nombre'],
                $row['descripcion'] !== null ? (string)$row['descripcion'] : null,
                $row['imagen'] !== null ? (string)$row['imagen'] : null
            );
        }
        $rs->free();

        return $res;
    }

    public function findById(int $id): ?CategoriaDTO
    {
        $sql = "SELECT id, nombre, descripcion, imagen
                FROM categorias
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findById categorias): " . $this->conn->error);

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (findById categorias): " . $stmt->error);
        }

        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        return new CategoriaDTO(
            (int)$row['id'],
            (string)$row['nombre'],
            $row['descripcion'] !== null ? (string)$row['descripcion'] : null,
            $row['imagen'] !== null ? (string)$row['imagen'] : null
        );
    }

    public function insert(string $nombre, ?string $descripcion, ?string $imagen): int
    {
        $sql = "INSERT INTO categorias (nombre, descripcion, imagen)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (insert categorias): " . $this->conn->error);

        $stmt->bind_param('sss', $nombre, $descripcion, $imagen);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (insert categorias): " . $stmt->error);
        }

        $id = (int)$this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    public function update(int $id, string $nombre, ?string $descripcion, ?string $imagen): bool
    {
        $sql = "UPDATE categorias
                SET nombre = ?, descripcion = ?, imagen = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (update categorias): " . $this->conn->error);

        $stmt->bind_param('sssi', $nombre, $descripcion, $imagen, $id);
        $ok = $stmt->execute();
        if (!$ok) {
            throw new RuntimeException("Error execute (update categorias): " . $stmt->error);
        }
        $stmt->close();
        return true;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categorias WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (delete categorias): " . $this->conn->error);

        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if (!$ok) {
            throw new RuntimeException("Error execute (delete categorias): " . $stmt->error);
        }
        $stmt->close();
        return true;
    }
}