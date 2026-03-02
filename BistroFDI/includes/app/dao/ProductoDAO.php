<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dto/ProductoDTO.php';

class ProductoDAO
{
    public function __construct(private mysqli $conn) {}

    /**
     * @return ProductoDTO[]
     */
    public function findAll(?int $idCategoria = null, bool $soloOfertados = false): array
    {
        $sql = "SELECT p.id, p.id_categoria, p.nombre, p.descripcion, p.precio_base, p.iva, p.disponible, p.ofertado
                FROM productos p";
        $types = '';
        $params = [];

        $where = [];
        if ($idCategoria !== null) {
            $where[] = "p.id_categoria = ?";
            $types .= 'i';
            $params[] = $idCategoria;
        }
        if ($soloOfertados) {
            $where[] = "p.ofertado = 1";
        }
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY p.nombre ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findAll productos): " . $this->conn->error);

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (findAll productos): " . $stmt->error);
        }

        $rs = $stmt->get_result();
        $res = [];
        while ($row = $rs->fetch_assoc()) {
            $idProd = (int)$row['id'];
            $imagenes = $this->findImagenesByProducto($idProd);

            $res[] = new ProductoDTO(
                $idProd,
                (int)$row['id_categoria'],
                (string)$row['nombre'],
                $row['descripcion'] !== null ? (string)$row['descripcion'] : null,
                (float)$row['precio_base'],
                (int)$row['iva'],
                (bool)$row['disponible'],
                (bool)$row['ofertado'],
                $imagenes
            );
        }
        $stmt->close();

        return $res;
    }

    public function findById(int $id): ?ProductoDTO
    {
        $sql = "SELECT id, id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado
                FROM productos
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findById productos): " . $this->conn->error);

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (findById productos): " . $stmt->error);
        }

        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        $imagenes = $this->findImagenesByProducto((int)$row['id']);

        return new ProductoDTO(
            (int)$row['id'],
            (int)$row['id_categoria'],
            (string)$row['nombre'],
            $row['descripcion'] !== null ? (string)$row['descripcion'] : null,
            (float)$row['precio_base'],
            (int)$row['iva'],
            (bool)$row['disponible'],
            (bool)$row['ofertado'],
            $imagenes
        );
    }

    public function insert(
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        bool $disponible,
        bool $ofertado
    ): int {
        $sql = "INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (insert productos): " . $this->conn->error);

        $disp = $disponible ? 1 : 0;
        $ofer = $ofertado ? 1 : 0;

        $stmt->bind_param('issdiii', $idCategoria, $nombre, $descripcion, $precioBase, $iva, $disp, $ofer);

        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (insert productos): " . $stmt->error);
        }

        $id = (int)$this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    public function update(
        int $id,
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        bool $disponible
    ): bool {
        $sql = "UPDATE productos
                SET id_categoria = ?, nombre = ?, descripcion = ?, precio_base = ?, iva = ?, disponible = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (update productos): " . $this->conn->error);

        $disp = $disponible ? 1 : 0;

        $stmt->bind_param('issdiii', $idCategoria, $nombre, $descripcion, $precioBase, $iva, $disp, $id);

        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (update productos): " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function setOfertado(int $id, bool $ofertado): bool
    {
        $sql = "UPDATE productos SET ofertado = ? WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (setOfertado): " . $this->conn->error);

        $ofer = $ofertado ? 1 : 0;
        $stmt->bind_param('ii', $ofer, $id);

        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (setOfertado): " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    /** @return string[] */
    public function findImagenesByProducto(int $idProducto): array
    {
        $sql = "SELECT ruta FROM productos_imagenes WHERE id_producto = ? ORDER BY id ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findImagenes): " . $this->conn->error);

        $stmt->bind_param('i', $idProducto);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (findImagenes): " . $stmt->error);
        }

        $rs = $stmt->get_result();
        $res = [];
        while ($row = $rs->fetch_assoc()) {
            $res[] = (string)$row['ruta'];
        }

        $stmt->close();
        return $res;
    }

    public function addImagen(int $idProducto, string $ruta): bool
    {
        $sql = "INSERT INTO productos_imagenes (id_producto, ruta) VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (addImagen): " . $this->conn->error);

        $stmt->bind_param('is', $idProducto, $ruta);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (addImagen): " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function deleteImagenByRuta(int $idProducto, string $ruta): bool
    {
        $sql = "DELETE FROM productos_imagenes WHERE id_producto = ? AND ruta = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (deleteImagenByRuta): " . $this->conn->error);

        $stmt->bind_param('is', $idProducto, $ruta);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error execute (deleteImagenByRuta): " . $stmt->error);
        }

        $stmt->close();
        return true;
    }
}