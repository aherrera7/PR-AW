<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

class CamarerosHelper
{
    private const ESTADOS_VISIBLES = ['recibido', 'listo cocina', 'terminado'];

    public static function esEstadoVisibleParaCamarero(string $estado): bool {
        return in_array($estado, self::ESTADOS_VISIBLES, true);
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
                'textoBoton' => '🥤 PREPARAR BEBIDAS',
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
        $soloBebidas = PedidoSA::soloBebidas($pedido->getId());

        return [
            'id' => $pedido->getId(),
            'numeroPedido' => $pedido->getNumeroPedido(),
            'tipoTexto' => $pedido->getTipo() === 'local' ? '🏠 LOCAL' : '🥡 LLEVAR',
            'hora' => date('H:i', strtotime($pedido->getFechaHora())),
            'idCliente' => $pedido->getIdCliente(),
            'totalFormateado' => number_format((float) $pedido->getTotal(), 2) . '€',
            'estado' => $estado,
            'soloBebidas' => $soloBebidas,
            'cardClass' => $soloBebidas ? 'order-card order-card--solo-bebidas' : $meta['cardClass'],
            'accion' => $meta['accion'],
            'textoBoton' => $meta['textoBoton'],
            'btnClass' => $meta['btnClass'],
            'mostrarRevision' => $meta['mostrarRevision'],
        ];
    }

    public static function formatearPedidosParaVista(array $pedidos): array {
        $pedidosFiltrados = array_filter($pedidos, fn($p) => self::esEstadoVisibleParaCamarero($p->getEstado()));
        return array_values(array_map(fn($p) => self::formatearPedidoParaVista($p), $pedidosFiltrados));
    }


    //control sobre las acciones del camarero 
    public static function procesarAccionPost(): ?string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
            $accion = $_POST['accion'] ?? '';
            
            if ($idPedido) {
                try {
                    switch ($accion) {
                        case 'cobrar':
                            PedidoSA::registrarPago($idPedido);
                            break;
                        case 'entregar':
                            PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_ENTREGADO);
                            break;
                    }
                    return null;
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        }
        return null;
    }
}