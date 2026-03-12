<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php'; // Necesario para sacar el nombre del producto

// 1. Verificación de acceso
if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

// 2. Obtener el ID del pedido desde la URL (método GET)
$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPedido) {
    // Si no hay ID válido, redirigimos a la lista
    header('Location: ' . RUTA_VISTAS . '/cliente/pedidos_listar_cliente.php');
    exit;
}

$errorAcceso = null;
$pedido = null;
$lineasPedido = [];

try {
    // 3. Obtener el pedido
    $pedido = PedidoSA::obtener($idPedido);
    
    if (!$pedido) {
        throw new Exception("El pedido solicitado no existe.");
    }

    // 4. Comprobación de seguridad: ¿Puede este usuario ver este pedido?
    $usuarioIdActual = (int)$_SESSION['usuario_id'];
    $esEmpleado = !empty($_SESSION['esGerente']) || !empty($_SESSION['esCocinero']) || !empty($_SESSION['esCamarero']);
    
    if ($pedido->getIdCliente() !== $usuarioIdActual && !$esEmpleado) {
        throw new Exception("No tienes permiso para ver los detalles de este pedido.");
    }

    // 5. Obtener los productos del pedido (las líneas)
    // NOTA: Ajusta el nombre de este método si en tu PedidoSA se llama distinto (ej: obtenerLineasPedido)
    // Asumimos que devuelve un array de PedidoProductoDTO
    $lineasPedido = PedidoSA::obtenerDetalle($idPedido); 

} catch (Exception $e) {
    $errorAcceso = $e->getMessage();
}

$tituloPagina = $pedido ? "Detalle del Pedido #" . $pedido->getNumeroPedido() : "Error en el Pedido";

ob_start();
?>

<section class="ger-wrap">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h1>Detalles del Pedido</h1>
        <button onclick="window.history.back();" class="btn btn-light" style="font-size: 0.9em;">← Volver</button>
    </div>

    <?php if ($errorAcceso): ?>
        <div class="alerta-error" style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($errorAcceso) ?>
        </div>
    <?php else: ?>

        <div class="card" style="margin-bottom: 25px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                <div>
                    <h3 style="margin: 0; color: #333;">Pedido #<?= $pedido->getNumeroPedido() ?></h3>
                    <p class="muted" style="margin: 5px 0 0 0; font-size: 0.9em;">
                        📅 <?= date('d/m/Y H:i', strtotime($pedido->getFechaHora())) ?>
                    </p>
                </div>
                <div style="text-align: right;">
                    <?php 
                        // Colores de estado estéticos
                        $estado = strtolower($pedido->getEstado());
                        $colorEstado = match($estado) {
                            'nuevo' => '#2196F3',
                            'en preparación', 'cocinando' => '#FF9800',
                            'listo cocina' => '#9C27B0',
                            'terminado', 'entregado' => '#4CAF50',
                            'cancelado' => '#F44336',
                            default => '#757575'
                        };
                    ?>
                    <span style="background: <?= $colorEstado ?>15; color: <?= $colorEstado ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid <?= $colorEstado ?>30;">
                        <?= strtoupper($estado) ?>
                    </span>
                    <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #555;">
                        Modalidad: <strong><?= ucfirst($pedido->getTipo()) ?></strong>
                    </p>
                </div>
            </div>

            <h4 style="margin-top: 0; margin-bottom: 15px; color: #444;">Productos</h4>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead style="background: #f9f9f9; border-bottom: 2px solid #ddd;">
                        <tr>
                            <th style="padding: 10px; font-size: 0.9em; color: #555;">Producto</th>
                            <th style="padding: 10px; font-size: 0.9em; color: #555; text-align: center;">Cantidad</th>
                            <th style="padding: 10px; font-size: 0.9em; color: #555; text-align: right;">Precio Ud.</th>
                            <th style="padding: 10px; font-size: 0.9em; color: #555; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lineasPedido)): ?>
                            <tr>
                                <td colspan="4" style="padding: 15px; text-align: center; color: #777;">No hay productos registrados en este pedido.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lineasPedido as $linea): 
                                // Sacamos el nombre del producto usando su SA
                                $producto = ProductoSA::obtener($linea->getIdProducto());
                                $nombreProducto = $producto ? $producto->getNombre() : "Producto Desconocido (ID: {$linea->getIdProducto()})";
                            ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px 10px;">
                                        <strong><?= htmlspecialchars($nombreProducto) ?></strong>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: center;">
                                        <?= $linea->getCantidad() ?>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: right;">
                                        <?= number_format($linea->getPrecioHistorico(), 2) ?>€
                                    </td>
                                    <td style="padding: 12px 10px; text-align: right; font-weight: bold;">
                                        <?= number_format($linea->getSubtotal(), 2) ?>€
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding: 15px 10px; text-align: right; font-size: 1.1em; color: #333;">
                                <strong>TOTAL:</strong>
                            </td>
                            <td style="padding: 15px 10px; text-align: right; font-size: 1.3em; color: #d32f2f; font-weight: bold;">
                                <?= number_format($pedido->getTotal(), 2) ?>€
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php endif; ?>
</section>

<style>
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .muted { color: #888; }
    .btn-light { background: #f0f0f0; border: 1px solid #ccc; padding: 6px 12px; border-radius: 4px; color: #333; text-decoration: none; cursor: pointer;}
    .btn-light:hover { background: #e4e4e4; }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';