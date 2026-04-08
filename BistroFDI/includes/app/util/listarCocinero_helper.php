<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

class ListarCocineroHelper
{
    //estados visibles en la cocina NO ESTOY SEGURA SI LISTO COCINA ES TOTALMENTE NECESARIO 
    private const ESTADOS_COCINA = ['en preparación', 'cocinando'];

    
    public static function obtenerPedidosCocina(): array {
        try {
            $todosLosPedidos = PedidoSA::listarTodos();
            $pedidosCocina = [];

            foreach ($todosLosPedidos as $p) {
                $estado = $p->getEstado();
                if (in_array($estado, self::ESTADOS_COCINA) && PedidoSA::tieneProductosCocina($p->getId())) {
                    $pedidosCocina[] = $p;
                }
            }
            
            return [
                'pedidos' => $pedidosCocina,
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'pedidos' => [],
                'error' => 'Error al cargar pedidos: ' . $e->getMessage()
            ];
        }
    }

 
    public static function obtenerCardClass(string $estado): string {
        return match($estado) {
            'recibido' => 'order-card order-card--recibido',
            'en preparación' => 'order-card order-card--preparacion',
            'cocinando' => 'order-card order-card--cocinando',
            'listo cocina' => 'order-card order-card--listo',
            default => 'order-card'
        };
    }

    public static function obtenerTextoBoton(string $estado): string {
        return ($estado === 'recibido') ? 'EMPEZAR' : 'GESTIONAR';
    }


    public static function formatearPedidoParaVista(object $pedido): array {
        $estado = $pedido->getEstado();
        
        return [
            'id' => $pedido->getId(),
            'numeroPedido' => $pedido->getNumeroPedido(),
            'tipoTexto' => $pedido->getTipo() === 'local' ? '🏠 LOCAL' : '🥡 LLEVAR',
            'hora' => date('H:i', strtotime($pedido->getFechaHora())),
            'estado' => $estado,
            'cardClass' => self::obtenerCardClass($estado),
            'textoBoton' => self::obtenerTextoBoton($estado),
        ];
    }

 
    public static function formatearPedidosParaVista(array $pedidos): array {
        return array_map(
            fn($pedido) => self::formatearPedidoParaVista($pedido),
            $pedidos
        );
    }

    //Lista de pedidos que se ven desde cocina ordenados por los estads
    public static function generarHtmlPedidos(array $pedidosFormateados): string {
        if (empty($pedidosFormateados)) {
            return '<div class="card kitchen-empty-state">
                        <p class="text-large text-muted-3">No hay pedidos pendientes en cocina.</p>
                        <p class="muted">¡Buen trabajo! Descansa un poco.</p>
                    </div>';
        }

        $html = '<div class="orders-grid kitchen-grid">';
        
        foreach ($pedidosFormateados as $p) {
            $html .= <<<HTML
                <div class="{$p['cardClass']}">
                    <div class="order-card-head kitchen-card-head">
                        <div>
                            <h3 class="title-reset">Pedido #{$p['numeroPedido']}</h3>
                            <span class="kitchen-order-type">
                                {$p['tipoTexto']}
                            </span>
                        </div>
                        <p class="muted kitchen-order-time">
                            <strong>{$p['hora']}</strong>
                        </p>
                    </div>

                    <div class="order-card-body">
                        <p class="kitchen-order-status">
                            Estado:
                            <span class="kitchen-order-status-value">
                                {$p['estado']}
                            </span>
                        </p>
                        
                        <div class="catalog-actions mt-15">
                            <a href="productos_pedido.php?id_pedido={$p['id']}" class="btn w-100 text-center">
                                {$p['textoBoton']}
                            </a>
                        </div>
                    </div>
                </div>
HTML;
        }
        
        $html .= '</div>';
        return $html;
    }
}