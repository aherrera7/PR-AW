<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dto/PedidoDTO.php';

// el cocinero entra y ve pedidos pendientes
// se queda con uno
// consulta qué lleva
// lo finaliza

class PedidoDAO {
    public function __construct(private mysqli $conn) {} // constructor con conexion

    /** @return PedidoDTO[] */
    public function findEnPreparacion(): array{
        $sql = "SELECT id, numero_pedido, id_cliente, fecha_hora, estado, tipo, total
                FROM pedidos
                WHERE estado = ?
                ORDER BY fecha_hora ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt)  throw new RuntimeException("Error prepare (findEnPreparacion pedidos): " . 1this->conn->error); 
        
        $estado = 'en preparación';
        $stmt->bind_param('s', $estado);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findEnPreparacion pedidos): " . $stmt->error); 

        $rs = $stmt->get_result();
        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new PedidoDTO(
                (int)$row['id'],
                (int)$row['numero_pedido'],
                $row['id_cliente'] !== null ? (int)$row['id_cliente'] : null,
                (string)$row['fecha_hora'],
                (string)$row['estado'],
                (string)$row['tipo'],
                (float)$row['total']
            );
        }
        $stmt->close();
        return $res;
    }

    // listado: pedidos disponibles para coger  
    /** @return PedidoDTO[] */
    //listado: pedidos que estan cocinandose  
    public function findCocinando(): array{
        $sql = "SELECT id, numero_pedido, id_cliente, fecha_hora, estado, tipo, total
                FROM pedidos
                WHERE estado = ?
                ORDER BY fecha_hora ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findCocinando pedidos): " . $this->conn->error);

        $estado = 'cocinando';
        $stmt->bind_param('s', $estado);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findCocinando pedidos): " . $stmt->error); 

        throw new RuntimeException("Error execute (findCocinando pedidos): " . $stmt->error);

        while ($row = $rs->fetch_assoc()) {
            $res[] = new PedidoDTO(
                (int)$row['id'],
                (int)$row['numero_pedido'],
                $row['id_cliente'] !== null ? (int)$row['id_cliente'] : null,
                (string)$row['fecha_hora'],
                (string)$row['estado'],
                (string)$row['tipo'],
                (float)$row['total']
            );
        }

        $stmt->close();
        return $res;      
    }

    // para recuperar un pedido concreto
    public function findById(int $id): ?PedidoDTO {
        $sql = "SELECT id, numero_pedido, id_cliente, fecha_hora, estado, tipo, total
                FROM pedidos
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql); 
        if (!$stmt) throw new RuntimeException("Error prepare (findById pedidos): " . $this->conn->error);

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findById pedidos): " . $stmt->error);

        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        return new PedidoDTO(
            (int)$row['id'],
            (int)$row['numero_pedido'],
            $row['id_cliente'] !== null ? (int)$row['id_cliente'] : null,
            (string)$row['fecha_hora'],
            (string)$row['estado'],
            (string)$row['tipo'],
            (float)$row['total']
        );
    }

    // cambio simple, hace el UPDATE sin comprobar el estado anterior
    public function updateEstado(int $id, string $nuevoEstado): bool {
        $sql = "UPDATE pedidos
                SET estado = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (updateEstado pedidos): " . $this->conn->error);

        $stmt->bind_param('si', $nuevoEstado, $id);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (updateEstado pedidos): " . $stmt->error);

        $stmt->close();
        return true;
    }

    // cambia el pedido si estaba realmente "en preparacion"
    public function updateEstadoSiCoincide(int $id, string $estadoEsperado, string $nuevoEstado): bool {
        $sql = "UPDATE pedidos
                SET estado = ?
                WHERE id = ? AND estado = ?";

        $stmt = $this->conn->prepare($sql); 
        if (!$stmt)  throw new RuntimeException("Error prepare (updateEstadoSiCoincide pedidos): " . $this->conn->error);

        $stmt->bind_param('sis', $nuevoEstado, $id, $estadoEsperado);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (updateEstadoSiCoincide pedidos): " . $stmt->error); 

        $afectadas = $stmt->affected_rows;
        $stmt->close();

        return $afectadas === 1;
    }

    /** @return array<int, array<string, mixed>> */
    // recuepra lineas de pedido desde "pedidos_productos"
    public function findDetalleByPedido(int $idPedido): array {
        $sql = "SELECT pp.id_pedido, pp.id_producto, pr.nombre, pp.cantidad, pp.precio_historico
                FROM pedidos_productos pp
                INNER JOIN productos pr ON pr.id = pp.id_producto
                WHERE pp.id_pedido = ?
                ORDER BY pr.nombre ASC";

        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findDetalleByPedido): " . $this->conn->error);

        $stmt->bind_param('i', $idPedido);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findDetalleByPedido): " . $stmt->error);

        $rs = $stmt->get_result();
        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = [
                'id_pedido' => (int)$row['id_pedido'],
                'id_producto' => (int)$row['id_producto'],
                'nombre' => (string)$row['nombre'],
                'cantidad' => (int)$row['cantidad'],
                'precio_historico' => (float)$row['precio_historico'],
            ];
        }

        $stmt->close();
        return $res;
    }
}

