<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

class CamareroPedidosHelper
{
    /**
     * Obtiene los datos del pedido y sus bebidas
     * @return array{pedido: PedidoDTO, productos: array, error: string|null}
     */
    public static function obtenerDatosPedido(int $idPedido): array {
        if ($idPedido <= 0) {
            return ['pedido' => null, 'productos' => [], 'error' => 'ID de pedido inválido.'];
        }

        try {
            $pedidoDTO = PedidoSA::obtener($idPedido);
            if (!$pedidoDTO) {
                return ['pedido' => null, 'productos' => [], 'error' => 'Pedido no encontrado.'];
            }

            $lineasDTO = PedidoSA::obtenerDetalle($idPedido);
            
            // Mostrar SOLO bebidas (es_cocina = 0)
            $productosMostrar = [];
            foreach ($lineasDTO as $linea) {
                $prod = ProductoSA::obtener($linea->getIdProducto());
                if ($prod && !$prod->getEsCocina()) {
                    $productosMostrar[] = [
                        'id' => $prod->getId(),
                        'nombre' => $prod->getNombre(),
                        'cantidad' => $linea->getCantidad(),
                        'img' => ($prod->getImagenes()[0] ?? 'default.jpg')
                    ];
                }
            }

            return [
                'pedido' => $pedidoDTO,
                'productos' => $productosMostrar,
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'pedido' => null,
                'productos' => [],
                'error' => 'Error al cargar el pedido: ' . $e->getMessage()
            ];
        }
    }

    
    public static function procesarFinalizar(int $idPedido): bool {
        if ($idPedido <= 0) return false;
        
        try {
            PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_TERMINADO);
            return true;
        } catch (Exception $e) {
            error_log("Error al finalizar pedido {$idPedido}: " . $e->getMessage());
            return false;
        }
    }


     //muestra las bebidas que hay que preparar 
    public static function generarTablaBebidasCamarero(array $productos): string {
        if (empty($productos)) {
            return '';
        }

        $html = '<table class="kitchen-products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="cell-center">Cantidad</th>
                            <th class="cell-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="lista-productos">';
        
        foreach ($productos as $item) {
            $imgUrl = h(RUTA_IMGS . '/' . ltrim((string)$item['img'], '/'));
            $nombre = h($item['nombre']);
            $cantidad = $item['cantidad'];
            $id = $item['id'];
            
            $html .= <<<HTML
                        <tr id="fila-{$id}" data-estado="pendiente" class="kitchen-product-row">
                            <td class="kitchen-product-main">
                                <img src="{$imgUrl}" class="kitchen-product-thumb" alt="{$nombre}">
                                <span class="kitchen-product-name">{$nombre}</span>
                            </td>
                            <td class="cell-center kitchen-product-qty">
                                <strong>x{$cantidad}</strong>
                            </td>
                            <td class="cell-right" id="accion-{$id}">
                                <button class="btn btn-success" onclick="camareroMarcarListo({$id})">
                                    LISTO
                                </button>
                            </td>
                        </tr>
HTML;
        }
        
        $html .= '</tbody>
                </table>';
        return $html;
    }

  //marcar el listo de las bebidas, saber si falta alguna y poner el pedido a finalizado 
    public static function generarScriptCamareroBebidas(int $numProductos, int $idPedido): string {
        return <<<JS
        <script>
        let camareroPendientes = {$numProductos};
        
        function camareroMarcarListo(id) {
            const fila = document.getElementById('fila-' + id);
            const celdaAccion = document.getElementById('accion-' + id);
            
            fila.classList.add('kitchen-product-row-ready');
            fila.setAttribute('data-estado', 'listo');
            
            celdaAccion.innerHTML = `
                <span class="kitchen-ready-label">✓ LISTO</span>
                <button class="btn btn-light kitchen-undo-btn" onclick="camareroDeshacerListo(\${id})">Deshacer</button>
            `;
            
            camareroPendientes--;
            camareroActualizarBoton();
        }
        
        function camareroDeshacerListo(id) {
            const fila = document.getElementById('fila-' + id);
            const celdaAccion = document.getElementById('accion-' + id);
            
            fila.classList.remove('kitchen-product-row-ready');
            fila.setAttribute('data-estado', 'pendiente');
            
            celdaAccion.innerHTML = `
                <button class="btn btn-success" onclick="camareroMarcarListo(\${id})">
                    LISTO
                </button>
            `;
            
            camareroPendientes++;
            camareroActualizarBoton();
        }
        
        function camareroActualizarBoton() {
            const btn = document.getElementById('btnFinalizarPedido');
            if (camareroPendientes === 0) {
                btn.disabled = false;
                btn.classList.remove('kitchen-finish-btn-disabled');
                btn.classList.add('kitchen-finish-btn-enabled');
            } else {
                btn.disabled = true;
                btn.classList.remove('kitchen-finish-btn-enabled');
                btn.classList.add('kitchen-finish-btn-disabled');
            }
        }
        
        function camareroFinalizarPedido(id) {
            if (confirm('¿Confirmas que todas las bebidas están listas para servir?')) {
                window.location.href = 'productos_pedido_camarero.php?id_pedido=' + id + '&finalizar=' + id;
            }
        }
        </script>
JS;
    }

    //mensaje no hay bebidas 
    public static function generarMensajeSinBebidasCamarero(): string {
        return '<div style="padding: 2rem; text-align: center; color: #666;">
                    <p>✅ No hay bebidas pendientes en este pedido.</p>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            camareroPendientes = 0;
                            camareroActualizarBoton();
                        });
                    </script>
                </div>';
    }
}