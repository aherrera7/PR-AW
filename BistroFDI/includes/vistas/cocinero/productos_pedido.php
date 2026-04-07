<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/util/prodPedidosCocinero_helper.php';


// Verificación de acceso (Cocinero o Gerente)
requireGerenteOCocinero();

$idCocineroActual = (int)$_SESSION['usuario_id'];
$nombreCocineroActual = $_SESSION['nombre'] ?? 'Cocinero';
$idPedido = (int)($_GET['id_pedido'] ?? 0);

// Procesar asignar cocinero
if (isset($_GET['asignar']) && $_GET['asignar'] == 1) {
    if (ProdPedidosCocineroHelper::procesarAsignar($idPedido, $idCocineroActual)) {
        header('Location: productos_pedido.php?id_pedido=' . $idPedido);
        exit;
    }
}

// Procesar finalizar pedido
if (isset($_GET['finalizar']) && (int)$_GET['finalizar'] > 0) {
    if (ProdPedidosCocineroHelper::procesarFinalizar($idPedido)) {
        header('Location: pedidos_listar_cocineros.php');
        exit;
    }
}

// Obtener datos del pedido
$datos = ProdPedidosCocineroHelper::obtenerDatosPedido($idPedido, $idCocineroActual);

if ($datos['error']) {
    die($datos['error']);
}

$pedidoDTO = $datos['pedido'];
$productosMostrar = $datos['productos'];
$idCocineroAsignado = $datos['idCocineroAsignado'];
$nombreCocineroAsignado = $datos['nombreCocineroAsignado'];
$numProductos = count($productosMostrar);

$tituloPagina = "Preparando Pedido #" . $pedidoDTO->getNumeroPedido();

ob_start();
?>

<section class="ger-wrap">
    <div class="kitchen-detail-head">
        <a href="pedidos_listar_cocineros.php" class="btn btn-light">← Volver</a>
        <h1 class="mb-0">Comanda #<?= $pedidoDTO->getNumeroPedido() ?></h1>
        <span class="kitchen-badge"><?= strtoupper(h($pedidoDTO->getTipo())) ?></span>

        <?php if ($idCocineroAsignado): ?>
            <div class="kitchen-assigned">
                <span class="kitchen-assigned-icon">👨‍🍳</span>
                <div>
                    <div class="kitchen-assigned-name">
                        <?= $idCocineroAsignado == $idCocineroActual ? 'Tú' : htmlspecialchars($nombreCocineroAsignado) ?>
                    </div>
                    <div class="kitchen-assigned-label">Cocinero asignado</div>
                </div>
            </div>
        <?php else: ?>
            <div class="kitchen-assigned-wrap">
                <a href="productos_pedido.php?id_pedido=<?= $idPedido ?>&asignar=1" class="btn kitchen-assign-btn">
                    📋 ASIGNARME ESTE PEDIDO
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card kitchen-products-card">
        <?php if (empty($productosMostrar)): ?>
            <?= ProdPedidosCocineroHelper::generarMensajeSinProductos() ?>
        <?php else: ?>
            <?= ProdPedidosCocineroHelper::generarTablaProductosCocina($productosMostrar) ?>
        <?php endif; ?>
    </div>

    <div class="kitchen-finish-wrap">
        <button id="btnFinalizarPedido" class="btn kitchen-finish-btn kitchen-finish-btn-disabled" onclick="cocineroFinalizarPedido(<?= $idPedido ?>)" disabled>
            PEDIDO COMPLETADO
        </button>
    </div>
</section>

<?= ProdPedidosCocineroHelper::generarScriptCocinero($numProductos, $idPedido) ?>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';