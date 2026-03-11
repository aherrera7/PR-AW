<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dto/PedidoDTO.php';
require_once RAIZ_APP . '/includes/app/dto/PedidoProductoDTO.php';
// Habla con la BD -> INSERT, SELECT, UPDATE
/*
- Crear pedido -> insertPedido()
- Insertar lineas pedido -> insertarLineaPeddido()
- Obtener soiguiente nº de pedido del día -> getSiguienteNumeroPedidoHoy()
- Buscar pedido por id -> findById()
- Obtener detalle de productos del pedido -> findLineasByPedido()
- Listar pedidos de un cliente -> findByCliente()
- Actualizar estado -> updateEstado()
*/

class PedidoDAO {

    public function __construct(private mysqli $conn) {}

    // Obtener soiguiente nº de pedido del día -> getSiguienteNumeroPedidoHoy()
    public function getSiguienteNumeroPedidoHoy(): int {
        $sql = "SELECT MAX(numero_pedido) AS max_num
                FROM pedidos
                WHERE DATE(fecha_hora) = CURDATE()";

        $rs = $this->conn->query($sql);
        if (!$rs) throw new RuntimeException("Error obteniendo numero pedido: " . $this->conn->error);
        
        $row = $rs->fetch_assoc();
        $max = $row['max_num'] ?? null;

        return $max !== null ? ((int)$max + 1) : 1;
    }

    // Crear pedido -> insertPedido()
    public function insertPedido(int $numeroPedido, int $idCliente, string $estado, string $tipo, float $total): int{ 
        $sql = "INSERT INTO pedidos (numero_pedido, id_cliente, estado, tipo, total)
                VALUES (?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (insertPedido): " . $this->conn->error);        
    
        $stmt->bind_param(
            'iissd',
            $numeroPedido,
            $idCliente,
            $estado,
            $tipo,
            $total
        );

        if (!$stmt->execute()) throw new RuntimeException("Error execute (insertPedido): " . $stmt->error);
    
        $id = (int)$this->conn->insert_id;

        $stmt->close();
        return $id;
    }

    // Insertar lineas pedido -> insertarLineaPedido()
    public function insertLineaPedido(int $idPedido, int $idProducto, int $cantidad, float $precioHistorico): bool{
        
        $sql = "INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, precio_historico)
                VALUES (?, ?, ?, ?)";   

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (insertLineaPedido): " . $this->conn->error);

        $stmt->bind_param(
            'iiid',
            $idPedido,
            $idProducto,
            $cantidad,
            $precioHistorico
        );

        if (!$stmt->execute())  throw new RuntimeException("Error execute (insertLineaPedido): " . $stmt->error);
 
        $stmt->close();
        return true;
    }

    // Buscar pedido por id -> findById()
    public function findById(int $id): ?PedidoDTO{
        $sql = "SELECT id, numero_pedido, id_cliente, fecha_hora, estado, tipo, total
                FROM pedidos
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new RuntimeException("Error prepare (findById pedido): " . $this->conn->error);

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findById pedido): " . $stmt->error);
        
        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();

        $stmt->close();

        if (!$row) return null;

        return new PedidoDTO(
            (int)$row['id'],
            (int)$row['numero_pedido'],
            (int)$row['id_cliente'],
            (string)$row['fecha_hora'],
            (string)$row['estado'],
            (string)$row['tipo'],
            (float)$row['total']
        );
    }

    /** @return PedidoProductoDTO[] */
    // Obtener detalle de productos del pedido -> findLineasByPedido()
    public function findLineasByPedido(int $idPedido): array{
        
        $sql = "SELECT id_pedido, id_producto, cantidad, precio_historico
                FROM pedidos_productos
                WHERE id_pedido = ?";

        $stmt = $this->conn->prepare($sql); 
        if (!$stmt) throw new RuntimeException("Error prepare (findLineasByPedido): " . $this->conn->error);

        $stmt->bind_param('i', $idPedido); 

        if (!$stmt->execute()) throw new RuntimeException("Error execute (findLineasByPedido): " . $stmt->error);

        $rs = $stmt->get_result();

        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new PedidoProductoDTO(
                (int)$row['id_pedido'],
                (int)$row['id_producto'],
                (int)$row['cantidad'],
                (float)$row['precio_historico']
            );
        }

        $stmt->close();
        return $res;
    }

    /** @return PedidoDTO[] */
    // Listar pedidos de un cliente -> findByCliente()
     public function findByCliente(int $idCliente): array{

        $sql = "SELECT id, numero_pedido, id_cliente, fecha_hora, estado, tipo, total
                FROM pedidos
                WHERE id_cliente = ?
                ORDER BY fecha_hora DESC";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) throw new RuntimeException("Error prepare (findByCliente): " . $this->conn->error);

        $stmt->bind_param('i', $idCliente);
        
        if (!$stmt->execute()) throw new RuntimeException("Error execute (findByCliente): " . $stmt->error);

        $rs = $stmt->get_result();

        $res = [];

        while ($row = $rs->fetch_assoc()) {
            $res[] = new PedidoDTO(
                (int)$row['id'],
                (int)$row['numero_pedido'],
                (int)$row['id_cliente'],
                (string)$row['fecha_hora'],
                (string)$row['estado'],
                (string)$row['tipo'],
                (float)$row['total']
            );
        }
        $stmt->close();
        return $res;
    }
    
    //Actualizar estado -> updateEstado()
     public function updateEstado(int $idPedido, string $estado): bool{
        $sql = "UPDATE pedidos
                SET estado = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql); 
       
        if (!$stmt) throw new RuntimeException("Error prepare (updateEstado): " . $this->conn->error);

        $stmt->bind_param('si', $estado, $idPedido); 

        if (!$stmt->execute()) throw new RuntimeException("Error execute (updateEstado): " . $stmt->error);

        $stmt->close();
        return true;
    }

    public function findAll(): array {
    // Los ordenamos por fecha, los más recientes primero
    $sql = "SELECT * FROM pedidos ORDER BY fecha_hora DESC";
    $rs = $this->conn->query($sql);

    if (!$rs) throw new RuntimeException("Error en findAll: " . $this->conn->error);

    $res = [];
    while ($row = $rs->fetch_assoc()) {
        $res[] = new PedidoDTO(
            (int)$row['id'],
            (int)$row['numero_pedido'],
            (int)$row['id_cliente'],
            (string)$row['fecha_hora'],
            (string)$row['estado'],
            (string)$row['tipo'],
            (float)$row['total']
        );
    }
    return $res;
    }   

}