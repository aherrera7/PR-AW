<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/PedidoDAO.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';

class PedidoSA {
    // Estados según definición en 02-tablas.sql
    public const ESTADO_NUEVO           = 'nuevo';
    public const ESTADO_RECIBIDO        = 'recibido';
    public const ESTADO_EN_PREPARACION  = 'en preparación';
    public const ESTADO_COCINANDO       = 'cocinando';
    public const ESTADO_LISTO_COCINA    = 'listo cocina';
    public const ESTADO_TERMINADO       = 'terminado';
    public const ESTADO_ENTREGADO       = 'entregado';

    private static function dao(): PedidoDAO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new PedidoDAO($conn);
    }
    
    public static function obtener(int $id): ?PedidoDTO {
        if ($id <= 0) throw new InvalidArgumentException('ID de pedido inválido.');
        return self::dao()->findById($id);
    }

    /** @return PedidoDTO[] */
    public static function listarPorCliente(int $idCliente): array {
        if ($idCliente <= 0) throw new InvalidArgumentException('ID de cliente inválido.');
        return self::dao()->findByCliente($idCliente);
    }

    /** * NUEVO: Lista pedidos pendientes para la vista de cocina
     * @return PedidoDTO[] 
     */
    public static function listarPedidosCocina(): array {
        // Filtramos por los estados en los que el cocinero debe intervenir
        $estadosInteres = [self::ESTADO_RECIBIDO, self::ESTADO_EN_PREPARACION, self::ESTADO_COCINANDO];
        
        $todos = self::dao()->findAll(); 
        return array_filter($todos, fn($p) => in_array($p->getEstado(), $estadosInteres));
    }

    /** @return PedidoProductoDTO[] */
    public static function obtenerDetalle(int $idPedido): array {
        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');
        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');

        return self::dao()->findLineasByPedido($idPedido);
    }

    public static function crearDesdeCarrito(int $idCliente, string $tipo, array $carrito): int {
        if ($idCliente <= 0) throw new InvalidArgumentException('Cliente inválido.');
        
        self::validarTipo($tipo);
        self::validarCarrito($carrito);

        $numeroPedido = self::dao()->getSiguienteNumeroPedidoHoy();
        $lineas = self::construirLineasPedido($carrito);
        
        $subtotalSinDescuento = self::calcularTotal($lineas);
        $mejorOferta = OfertaSA::obtenerMejorOfertaAplicable($carrito);

        $idOfertaAplicada = null;
        $descuentoTotal = 0.0;

        if ($mejorOferta !== null) {
            $idOfertaAplicada = intval(strval($mejorOferta['id_oferta'] ?? '0'));
            $descuentoTotal = round(floatval(strval($mejorOferta['descuento_total'] ?? '0')), 2);
        }

        $total = round($subtotalSinDescuento - $descuentoTotal, 2);

        if ($total < 0) {
            $total = 0.0;
        }

        $idPedido = self::dao()->insertPedido(
            $numeroPedido,
            $idCliente,
            $idCocinero, 
            $idOfertaAplicada,
            self::ESTADO_RECIBIDO, // El pedido entra como recibido tras crearse
            $tipo,
            $subtotalSinDescuento,
            $descuentoTotal,
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


    /**
     * NUEVO: Cambia el estado de un pedido (útil para el flujo de cocina)
     */
    public static function cambiarEstado(int $idPedido, string $nuevoEstado): bool {
        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');
        
        // Validar que el estado existe en el ENUM de la BD
        $estadosValidos = [
            self::ESTADO_NUEVO, self::ESTADO_RECIBIDO, self::ESTADO_EN_PREPARACION, 
            self::ESTADO_COCINANDO, self::ESTADO_LISTO_COCINA, self::ESTADO_TERMINADO, self::ESTADO_ENTREGADO
        ];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            throw new InvalidArgumentException('Estado de pedido no válido.');
        }

        return self::dao()->updateEstado($idPedido, $nuevoEstado);
    }

    public static function registrarPago(int $idPedido): bool {
        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');

        // Si ya está pagado/recibido, lo pasamos a preparación
        if ($pedido->getEstado() !== self::ESTADO_RECIBIDO) return false;

        $result = self::dao()->updateEstado($idPedido, self::ESTADO_EN_PREPARACION);
    
        // Si solo tiene bebidas, pasar directamente a LISTO_COCINA
        if ($result && self::soloBebidas($idPedido)) {
            self::dao()->updateEstado($idPedido, self::ESTADO_LISTO_COCINA);
        }
    
        return $result;
        
    }

    private static function validarTipo(string $tipo): void {
        if ($tipo !== 'local' && $tipo !== 'llevar') throw new InvalidArgumentException('Tipo de pedido inválido.');
    }
 
    private static function validarCarrito(array $carrito): void {
        if (empty($carrito)) throw new InvalidArgumentException('El carrito está vacío.');

        foreach ($carrito as $idProducto => $cantidad) {
            if ((int)$idProducto <= 0 || (int)$cantidad <= 0) {
                throw new InvalidArgumentException('Producto o cantidad inválida en el carrito.');
            }
        }
    }

    /** @return PedidoProductoDTO[] */
    private static function construirLineasPedido(array $carrito): array {
        $lineas = [];
        foreach ($carrito as $idProducto => $cantidad) {
            $producto = ProductoSA::obtener((int)$idProducto);

            if ($producto === null) throw new InvalidArgumentException('El producto no existe.');
            if (!$producto->isDisponible()) throw new InvalidArgumentException('Producto no disponible.');
        
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
        foreach ($lineas as $linea) {
            $total += $linea->getSubtotal();
        }
        return round($total, 2);
    }

    public static function listarTodos(): array {
        return self::dao()->findAll();
    }


    public static function actualizarEstado(int $idPedido, string $nuevoEstado): bool {
        if ($idPedido <= 0) return false;
        
        return self::dao()->updateEstado($idPedido, $nuevoEstado);
    }


    public static function asignarCocinero(int $idPedido, int $idCocinero): bool {
    if ($idPedido <= 0 || $idCocinero <= 0) return false;
    
    return self::dao()->updateCocinero($idPedido, $idCocinero);
}

public static function soloBebidas(int $idPedido): bool {
    $lineas = self::obtenerDetalle($idPedido);
    foreach ($lineas as $linea) {
        $producto = ProductoSA::obtener($linea->getIdProducto());
        if ($producto && $producto->getEsCocina()) {
            return false;
        }
    }
    return true;
}

public static function tieneProductosCocina(int $idPedido): bool {
    return !self::soloBebidas($idPedido);
}

}