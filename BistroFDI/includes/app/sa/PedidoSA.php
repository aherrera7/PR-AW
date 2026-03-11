<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/PedidoDAO.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

/*
SOBRE PEDIDOS REALES
- obtener un pedido
- listar pedidos de un cliente
- listar pedidos de un cliente para gerente
- obtener detalle de un pedido
- crear pedido desde carrito
- registrar pago

SOBRE EL FLUJO
- generar número de pedido diario
- calcular total con IVA
- insertar cabecera y líneas
- cambiar de recibido a en preparación
*/

class PedidoSA {
    private const ESTADO_RECIBIDO = 'recibido';
    private const ESTADO_EN_PREPARACION = 'en preparación';

    private static function dao(): PedidoDAO{
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new PedidoDAO($conn);
    }
    
    public static function obtener(int $id): ?PedidoDTO{
        if ($id <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        return self::dao()->findById($id);
    }

    /** @return PedidoDTO[] */
    public static function listarPorCliente(int $idCliente): array{
        if ($idCliente <= 0) throw new InvalidArgumentException('ID de cliente inválido.');

        return self::dao()->findByCliente($idCliente);
    }

    /** @return PedidoProductoDTO[] */
    public static function obtenerDetalle(int $idPedido): array{
        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null)throw new InvalidArgumentException('El pedido no existe.');

        return self::dao()->findLineasByPedido($idPedido);
    }

    public static function crearDesdeCarrito(int $idCliente, string $tipo, array $carrito): int{
        if ($idCliente <= 0) throw new InvalidArgumentException('Cliente inválido.');
        
        self::validarTipo($tipo);
        self::validarCarrito($carrito);

        $numeroPedido = self::dao()->getSiguienteNumeroPedidoHoy();
        $lineas = self::construirLineasPedido($carrito);
        $total = self::calcularTotal($lineas);

        $idPedido = self::dao()->insertPedido(
            $numeroPedido,
            $idCliente,
            self::ESTADO_RECIBIDO,
            $tipo,
            $total
        );

        foreach ($lineas as $linea) {
            self::dao()->insertLineaPedido(
                $idPedido,
                $linea->getIdProducto(),
                $linea->getCantidad(),
                $linea->getPrecioHistorico()
            );
        }

        return $idPedido;
    }

    public static function registrarPago(int $idPedido): bool{
        
        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        $pedido = self::dao()->findById($idPedido); 
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');

        if ($pedido->getEstado() !== self::ESTADO_RECIBIDO) return false;

        return self::dao()->updateEstado($idPedido, self::ESTADO_EN_PREPARACION);
    }

    private static function validarTipo(string $tipo): void {
        if ($tipo !== 'local' && $tipo !== 'llevar') throw new InvalidArgumentException('Tipo de pedido inválido.');
    }
 
     private static function validarCarrito(array $carrito): void{
        if (empty($carrito)) throw new InvalidArgumentException('El carrito está vacío.');

        foreach ($carrito as $idProducto => $cantidad) {
            $idProducto = (int)$idProducto;
            $cantidad = (int)$cantidad;

            if ($idProducto <= 0) throw new InvalidArgumentException('Producto inválido en el carrito.');
            if ($cantidad <= 0) throw new InvalidArgumentException('Cantidad inválida en el carrito.');
        }
    }

    /** @return PedidoProductoDTO[] */
    private static function construirLineasPedido(array $carrito): array {
        $lineas = [];

       foreach ($carrito as $idProducto => $cantidad) {
            $producto = ProductoSA::obtener((int)$idProducto);

            if ($producto === null) throw new InvalidArgumentException('Uno de los productos del carrito no existe.');
            
            if (!$producto->isDisponible()) throw new InvalidArgumentException('Uno de los productos del carrito no está disponible.');
        
            $lineas[] = new PedidoProductoDTO(
                0,
                (int)$idProducto,
                (int)$cantidad,
                $producto->getPrecioFinal()
            );
        }
        return $lineas;
    }

    /** @param PedidoProductoDTO[] $lineas */
    private static function calcularTotal(array $lineas): float {
        $total = 0.0;

        foreach ($lineas as $linea) $total += $linea->getSubtotal();

        return round($total, 2);
    }
 
}