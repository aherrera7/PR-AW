<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/PedidoDAO.php';

class PedidoSA {
    private static function dao(): PedidoDAO {
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new PedidoDAO($conn);
    }

    /** @return PedidoDTO[] */
    public static function listarEnPreparacion(): array {
        // devuelve todos los pedidos que estan esperando a que cocina los coja
        return self::dao()->findEnPreparacion();
    }

    /** @return PedidoDTO[] */
    public static function listarCocinando(): array {
        // devuelve los pedidos ya en cocina
        return self::dao()->findCocinando();
    }

    public static function obtener(int $id): ?PedidoDTO {
        if ($id <= 0) throw new InvalidArgumentException('ID de pedido inválido.');
        return self::dao()->findById($id);
    }

    /** @return array<int, array<string, mixed>> */
    public static function obtenerDetalle(int $idPedido): array {
        // primero comprueba: que el id sea valido y que el pedido exista, y luego saca las lineas del pedido    

        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');
        
        return self::dao()->findDetalleByPedido($idPedido);
    }

    public static function cogerPedido(int $idPedido): bool {
        // solo se puede coger si esta EN PREPARACION

        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');

        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');

        if ($pedido->getEstado() !== 'en preparación') return false;
        
        return self::dao()->updateEstadoSiCoincide(
            $idPedido,
            'en preparación',
            'cocinando'
        );
    }

    public static function finalizarPedido(int $idPedido): bool {
        // solo se puede finalizar si esta en COCINANDO

        if ($idPedido <= 0) throw new InvalidArgumentException('ID de pedido inválido.');
        
        $pedido = self::dao()->findById($idPedido);
        if ($pedido === null) throw new InvalidArgumentException('El pedido no existe.');
        
        if ($pedido->getEstado() !== 'cocinando') return false;
        
        return self::dao()->updateEstadoSiCoincide(
            $idPedido,
            'cocinando',
            'listo cocina'
        );
    }
}