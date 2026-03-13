<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

// 1. Verificación de acceso
if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

// 2. Obtener el ID del pedido desde la URL (método GET)
$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPedido) {
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

    // 4. Comprobación de seguridad
    $usuarioIdActual = (int)$_SESSION['usuario_id'];
    $esEmpleado = !empty($_SESSION['esGerente']) || !empty($_SESSION['esCocinero']) || !empty($_SESSION['esCamarero']);
    
    if ($pedido->getIdCliente() !== $usuarioIdActual && !$esEmpleado) {
        throw new Exception("No tienes permiso para ver los detalles de este pedido.");
    }

    // 5. Obtener las líneas del pedido
    $lineasPedido = PedidoSA::obtenerDetalle($idPedido);

} catch (Exception $e) {
    $errorAcceso = $e->getMessage();
}

$tituloPagina = $pedido ? "Detalle del Pedido #" . $pedido->getNumeroPedido() : "Error en el Pedido";

ob_start();
?>

<section class="ger-wrap">
    
    <div class="page-head">
        <h1>Detalles del Pedido</h1>
        <button onclick="window.history.back();" class="btn btn-light page-head-back">← Volver</button>
    </div>

    <?php if ($errorAcceso): ?>
        <div class="alert-error">
            <?= htmlspecialchars($errorAcceso) ?>
        </div>
    <?php else: ?>

        <div class="card card-soft mb-20 p-20">
            <div class="detail-card-head">
                <div>
                    <h3 class="title-reset text-dark">Pedido #<?= $pedido->getNumeroPedido() ?></h3>
                    <p class="muted order-time">
                        📅 <?= date('d/m/Y H:i', strtotime($pedido->getFechaHora())) ?>
                    </p>
                </div>
                <div class="detail-status-side">
                    <?php 
                        $estado = strtolower($pedido->getEstado());
                        $statusClass = match($estado) {
                            'nuevo' => 'status-badge status-badge--nuevo',
                            'en preparación', 'cocinando' => 'status-badge status-badge--preparacion',
                            'listo cocina' => 'status-badge status-badge--listo',
                            'terminado', 'entregado' => 'status-badge status-badge--finalizado',
                            'cancelado' => 'status-badge status-badge--cancelado',
                            default => 'status-badge status-badge--default'
                        };
                    ?>
                    <span class="<?= $statusClass ?>">
                        <?= strtoupper($estado) ?>
                    </span>
                    <p class="pedido-detail-meta">
                        Modalidad: <strong><?= ucfirst($pedido->getTipo()) ?></strong>
                    </p>
                </div>
            </div>

            <h4 class="pedido-detail-subtitle">Productos</h4>
            
            <div class="table-wrap">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="cell-center">Cantidad</th>
                            <th class="cell-right">Precio Ud.</th>
                            <th class="cell-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lineasPedido)): ?>
                            <tr>
                                <td colspan="4" class="order-table-empty">No hay productos registrados en este pedido.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lineasPedido as $linea): 
                                $producto = ProductoSA::obtener($linea->getIdProducto());
                                $nombreProducto = $producto ? $producto->getNombre() : "Producto Desconocido (ID: {$linea->getIdProducto()})";
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($nombreProducto) ?></strong>
                                    </td>
                                    <td class="cell-center">
                                        <?= $linea->getCantidad() ?>
                                    </td>
                                    <td class="cell-right">
                                        <?= number_format($linea->getPrecioHistorico(), 2) ?>€
                                    </td>
                                    <td class="cell-right">
                                        <strong><?= number_format($linea->getSubtotal(), 2) ?>€</strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="order-total-label">
                                <strong>TOTAL:</strong>
                            </td>
                            <td class="order-total-value">
                                <?= number_format($pedido->getTotal(), 2) ?>€
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';