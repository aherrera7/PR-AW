<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

class ProdPedidosCocineroHelper
{
  
    public static function obtenerDatosPedido(int $idPedido, int $idCocineroActual): array {
        if ($idPedido <= 0) {
            return ['pedido' => null, 'productos' => [], 'idCocineroAsignado' => null, 'nombreCocineroAsignado' => null, 'error' => 'ID de pedido inválido.'];
        }

        try {
            $pedidoDTO = PedidoSA::obtener($idPedido);
            if (!$pedidoDTO) {
                return ['pedido' => null, 'productos' => [], 'idCocineroAsignado' => null, 'nombreCocineroAsignado' => null, 'error' => 'Pedido no encontrado.'];
            }

            $idCocineroAsignado = $pedidoDTO->getIdCocinero();
            $nombreCocineroAsignado = null;
            if ($idCocineroAsignado) {
                $usuarioSA = new UsuarioSA();
                $cocinero = $usuarioSA->getById($idCocineroAsignado);
                $nombreCocineroAsignado = $cocinero ? $cocinero->getNombre() : 'Cocinero #' . $idCocineroAsignado;
            }

            $lineasDTO = PedidoSA::obtenerDetalle($idPedido);
            
            // Mostrar SOLO productos de cocina (es_cocina = 1)
            $productosMostrar = [];
            foreach ($lineasDTO as $linea) {
                $prod = ProductoSA::obtener($linea->getIdProducto());
                if ($prod && $prod->getEsCocina()) {
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
                'idCocineroAsignado' => $idCocineroAsignado,
                'nombreCocineroAsignado' => $nombreCocineroAsignado,
                'error' => null
            ];

        } catch (Exception $e) {
            return [
                'pedido' => null,
                'productos' => [],
                'idCocineroAsignado' => null,
                'nombreCocineroAsignado' => null,
                'error' => 'Error al cargar el pedido: ' . $e->getMessage()
            ];
        }
    }

    //Procesa la acción de asignar cocinero
     
    public static function procesarAsignar(int $idPedido, int $idCocineroActual): bool {
        if ($idPedido <= 0 || $idCocineroActual <= 0) return false;
        
        try {
            PedidoSA::asignarCocinero($idPedido, $idCocineroActual);
            return true;
        } catch (Exception $e) {
            error_log("Error al asignar cocinero: " . $e->getMessage());
            return false;
        }
    }

    //finalizar pedido y pasar a listo cocina
    public static function procesarFinalizar(int $idPedido): bool {
        if ($idPedido <= 0) return false;
        
        try {
            PedidoSA::actualizarEstado($idPedido, PedidoSA::ESTADO_LISTO_COCINA);
            return true;
        } catch (Exception $e) {
            error_log("Error al finalizar pedido {$idPedido}: " . $e->getMessage());
            return false;
        }
    }

    
    public static function generarTablaProductosCocina(array $productos): string {
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
                                <button class="btn btn-success" onclick="cocineroMarcarListo({$id})">
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

    //mensaje no hay productos de cocina
    public static function generarMensajeSinProductos(): string {
        return '<div style="padding: 2rem; text-align: center; color: #666;">
                    <p>🥤 Este pedido solo contiene bebidas o productos que no requieren cocina.</p>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            cocineroPendientes = 0;
                            cocineroActualizarBoton();
                        });
                    </script>
                </div>';
    }

    //marcar listo y finalizar pedido para pasar a listococina
    public static function generarScriptCocinero(int $numProductos, int $idPedido): string {
        return <<<JS
        <script>
        let cocineroPendientes = {$numProductos};
        
        function cocineroMarcarListo(id) {
            const fila = document.getElementById('fila-' + id);
            const celdaAccion = document.getElementById('accion-' + id);
            
            fila.classList.add('kitchen-product-row-ready');
            fila.setAttribute('data-estado', 'listo');
            
            celdaAccion.innerHTML = `
                <span class="kitchen-ready-label">✓ LISTO</span>
                <button class="btn btn-light kitchen-undo-btn" onclick="cocineroDeshacerListo(\${id})">Deshacer</button>
            `;
            
            cocineroPendientes--;
            cocineroActualizarBoton();
        }
        
        function cocineroDeshacerListo(id) {
            const fila = document.getElementById('fila-' + id);
            const celdaAccion = document.getElementById('accion-' + id);
            
            fila.classList.remove('kitchen-product-row-ready');
            fila.setAttribute('data-estado', 'pendiente');
            
            celdaAccion.innerHTML = `
                <button class="btn btn-success" onclick="cocineroMarcarListo(\${id})">
                    LISTO
                </button>
            `;
            
            cocineroPendientes++;
            cocineroActualizarBoton();
        }
        
        function cocineroActualizarBoton() {
            const btn = document.getElementById('btnFinalizarPedido');
            if (cocineroPendientes === 0) {
                btn.disabled = false;
                btn.classList.remove('kitchen-finish-btn-disabled');
                btn.classList.add('kitchen-finish-btn-enabled');
            } else {
                btn.disabled = true;
                btn.classList.remove('kitchen-finish-btn-enabled');
                btn.classList.add('kitchen-finish-btn-disabled');
            }
        }
        
        function cocineroFinalizarPedido(id) {
            if (confirm('¿Confirmas que toda la comanda está lista para ser servida?')) {
                window.location.href = 'productos_pedido.php?id_pedido=' + id + '&finalizar=' + id;
            }
        }
        </script> 
JS;
    }
}