<?php
declare(strict_types=1);

require_once __DIR__ . '/../dto/OfertaDTO.php';
require_once __DIR__ . '/../dto/OfertaProductoDTO.php';

class OfertaDAO {
    public function __construct(private mysqli $conn){}

    public function findById(int $id): ?OfertaDTO {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento, activa
                FROM ofertas
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Error prepare (findById oferta): ' . $this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) { throw new RuntimeException('Error execute (findById oferta): ' . $stmt->error); }

        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();
        $stmt->close();

        if (!$row) { return null;}

        return new OfertaDTO(
            intval($row['id']),
            strval($row['nombre']),
            strval($row['descripcion']),
            strval($row['fecha_inicio']),
            strval($row['fecha_fin']),
            floatval($row['descuento']),
            boolval($row['activa'])
        );
    }

    public function findAll(): array
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento, activa
                FROM ofertas
                ORDER BY fecha_inicio DESC, id DESC";

        $rs = $this->conn->query($sql);
        if (!$rs) { throw new RuntimeException('Error en findAll ofertas: ' . $this->conn->error); }

        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new OfertaDTO(
                intval($row['id']),
                strval($row['nombre']),
                strval($row['descripcion']),
                strval($row['fecha_inicio']),
                strval($row['fecha_fin']),
                floatval($row['descuento']),
                boolval($row['activa'])
            );
        }

        return $res;
    }

    public function findActivasHoy(): array
    {
        $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, descuento, activa
                FROM ofertas
                WHERE activa = 1
                  AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
                ORDER BY fecha_inicio DESC, id DESC";

        $rs = $this->conn->query($sql);
        if (!$rs) { throw new RuntimeException('Error en findActivasHoy ofertas: ' . $this->conn->error); }

        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new OfertaDTO(
                intval($row['id']),
                strval($row['nombre']),
                strval($row['descripcion']),
                strval($row['fecha_inicio']),
                strval($row['fecha_fin']),
                floatval($row['descuento']),
                boolval($row['activa'])
            );
        }

        return $res;
    }

    public function findProductosByOferta(int $idOferta): array
    {
        $sql = "SELECT id_oferta, id_producto, cantidad
                FROM ofertas_productos
                WHERE id_oferta = ?
                ORDER BY id_producto ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException('Error prepare (findProductosByOferta): ' . $this->conn->error); }

        $stmt->bind_param('i', $idOferta);

        if (!$stmt->execute()) { throw new RuntimeException('Error execute (findProductosByOferta): ' . $stmt->error); }

        $rs = $stmt->get_result();
        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new OfertaProductoDTO(
                intval($row['id_oferta']),
                intval($row['id_producto']),
                intval($row['cantidad'])
            );
        }

        $stmt->close();
        return $res;
    }

    public function insertOferta(
        string $nombre,
        string $descripcion,
        string $fechaInicio,
        string $fechaFin,
        float $descuento,
        bool $activa
    ): int {
        $sql = "INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, descuento, activa)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Error prepare (insertOferta): ' . $this->conn->error);
        }

        $activaInt = $activa ? 1 : 0;

        $stmt->bind_param(
            'ssssdi',
            $nombre,
            $descripcion,
            $fechaInicio,
            $fechaFin,
            $descuento,
            $activaInt
        );

        if (!$stmt->execute()) {
            throw new RuntimeException('Error execute (insertOferta): ' . $stmt->error);
        }

        $id = intval($this->conn->insert_id);
        $stmt->close();

        return $id;
    }

    public function insertOfertaProducto(int $idOferta, int $idProducto, int $cantidad): bool {
        $sql = "INSERT INTO ofertas_productos (id_oferta, id_producto, cantidad)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Error prepare (insertOfertaProducto): ' . $this->conn->error);
        }

        $stmt->bind_param('iii', $idOferta, $idProducto, $cantidad);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error execute (insertOfertaProducto): ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function updateOferta(
        int $id,
        string $nombre,
        string $descripcion,
        string $fechaInicio,
        string $fechaFin,
        float $descuento,
        bool $activa
    ): bool {
        $sql = "UPDATE ofertas
                SET nombre = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, descuento = ?, activa = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) { throw new RuntimeException('Error prepare (updateOferta): ' . $this->conn->error); }

        $activaInt = $activa ? 1 : 0;

        $stmt->bind_param(
            'ssssdii',
            $nombre,
            $descripcion,
            $fechaInicio,
            $fechaFin,
            $descuento,
            $activaInt,
            $id
        );

        if (!$stmt->execute()) {
            throw new RuntimeException('Error execute (updateOferta): ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function deleteOfertaProductos(int $idOferta): bool
    {
        $sql = "DELETE FROM ofertas_productos
                WHERE id_oferta = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Error prepare (deleteOfertaProductos): ' . $this->conn->error);
        }

        $stmt->bind_param('i', $idOferta);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error execute (deleteOfertaProductos): ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function deleteOferta(int $id): bool
    {
        $sql = "DELETE FROM ofertas
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Error prepare (deleteOferta): ' . $this->conn->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            throw new RuntimeException('Error execute (deleteOferta): ' . $stmt->error);
        }

        $stmt->close();
        return true;
    }
}