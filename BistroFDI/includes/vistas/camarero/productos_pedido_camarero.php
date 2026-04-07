<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/util/prodPedidosCamarero_helper.php';

// Verificación de acceso (Camarero o Gerente)
requireGerenteOCamarero();

$idCamareroActual = (int)($_SESSION['usuario_id'] ?? 0);
$nombreCamareroActual = $_SESSION['nombre'] ?? 'Camarero';
$idPedido = (int)($_GET['id_pedido'] ?? 0);

// Procesar asignar
if (isset($_GET['asignar']) && $_GET['asignar'] == 1) {
    CamareroPedidosHelper::procesarAsignar($idPedido);
    header('Location: productos_pedido_camarero.php?id_pedido=' . $idPedido);
    exit;
}

// Procesar finalizar pedido
if (isset($_GET['finalizar']) && (int)$_GET['finalizar'] > 0) {
    if (CamareroPedidosHelper::procesarFinalizar($idPedido)) {
        header('Location: camarero_pedidos.php');
        exit;
    }
}

//llama al helper para obtenr datos del pedido 
$datos = CamareroPedidosHelper::obtenerDatosPedido($idPedido);

if ($datos['error']) {
    die($datos['error']);
}

$pedidoDTO = $datos['pedido'];
$productosMostrar = $datos['productos'];
$numProductos = count($productosMostrar);

$tituloPagina = "Preparando Bebidas - Pedido #" . $pedidoDTO->getNumeroPedido();

ob_start();
?>

<section class="ger-wrap">
    <div class="kitchen-detail-head">
        <a href="camarero_pedidos.php" class="btn btn-light">← Volver</a>
        <h1 class="mb-0">Bebidas Pedido #<?= $pedidoDTO->getNumeroPedido() ?></h1>
        <span class="kitchen-badge"><?= strtoupper(h($pedidoDTO->getTipo())) ?></span>
    </div>

    <div class="card kitchen-products-card">
        <?php if (empty($productosMostrar)): ?>
            <?= CamareroPedidosHelper::generarMensajeSinBebidasCamarero() ?>
        <?php else: ?>
            <?= CamareroPedidosHelper::generarTablaBebidasCamarero($productosMostrar) ?>
        <?php endif; ?>
    </div>

    <div class="kitchen-finish-wrap">
        <button id="btnFinalizarPedido" class="btn kitchen-finish-btn kitchen-finish-btn-disabled" onclick="camareroFinalizarPedido(<?= $idPedido ?>)" disabled>
            LISTO
        </button>
    </div>
</section>

<?= CamareroPedidosHelper::generarScriptCamareroBebidas($numProductos, $idPedido) ?>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';