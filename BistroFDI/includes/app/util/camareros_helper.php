<?php
declare(strict_types=1);

final class CamareroHelper
{
    private const ESTADOS_VISIBLES = ['recibido', 'listo cocina', 'terminado'];

    public static function esEstadoVisibleParaCamarero(string $estado): bool {
        return in_array($estado, self::ESTADOS_VISIBLES, true);
    }

    public static function filtrarPedidosParaCamarero(array $pedidos): array {
        return array_values(array_filter($pedidos, static fn($pedido) => self::esEstadoVisibleParaCamarero($pedido->getEstado())));
    }

    public static function obtenerMetaEstado(string $estado): array {
        return match ($estado) {
            'recibido' => [
                'cardClass' => 'order-card order-card--recibido',
                'accion' => 'cobrar',
                'textoBoton' => '💰 COBRAR',
                'btnClass' => 'btn-order btn-order--recibido',
                'mostrarRevision' => false,
            ],
            'listo cocina' => [
                'cardClass' => 'order-card order-card--listo',
                'accion' => 'preparar_entrega',
                'textoBoton' => '📦 PREPARAR ENTREGA',
                'btnClass' => 'btn-order btn-order--listo',
                'mostrarRevision' => true,
            ],
            'terminado' => [
                'cardClass' => 'order-card order-card--terminado',
                'accion' => 'entregar',
                'textoBoton' => '✅ ENTREGAR',
                'btnClass' => 'btn-order btn-order--terminado',
                'mostrarRevision' => false,
            ],
            default => [
                'cardClass' => 'order-card',
                'accion' => '',
                'textoBoton' => '',
                'btnClass' => 'btn-order',
                'mostrarRevision' => false,
            ],
        };
    }

    public static function formatearPedidoParaVista(object $pedido): array {
        $estado = $pedido->getEstado();
        $meta = self::obtenerMetaEstado($estado);

        return [
            'id' => $pedido->getId(),
            'numeroPedido' => $pedido->getNumeroPedido(),
            'tipoTexto' => $pedido->getTipo() === 'local' ? 'LOCAL' : 'LLEVAR',
            'hora' => date('H:i', strtotime($pedido->getFechaHora())),
            'idCliente' => $pedido->getIdCliente(),
            'totalFormateado' => number_format((float) $pedido->getTotal(), 2) . '€',
            'estado' => $estado,
            'cardClass' => $meta['cardClass'],
            'accion' => $meta['accion'],
            'textoBoton' => $meta['textoBoton'],
            'btnClass' => $meta['btnClass'],
            'mostrarRevision' => $meta['mostrarRevision'],
        ];
    }

    public static function formatearPedidosParaVista(array $pedidos): array {
        $pedidosFiltrados = self::filtrarPedidosParaCamarero($pedidos);
        return array_map(
            static fn($pedido) => self::formatearPedidoParaVista($pedido),
            $pedidosFiltrados
        );
    }
}