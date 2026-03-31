<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/util/camareros_helper.php';

//1. Verificación de acceso (Camarero o Gerente)
requireGerenteOCamarero();

$nombreCamarero = $_SESSION['nombre'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'avatares/default.jpg';
$avatarCamareroUrl = RUTA_IMGS . '/' . ltrim((string)$avatarCamarero, '/');

$tituloPagina = "Estado de los pedidos (Camarero)";

//2. Procesamiento de acciones: Según el botón que se pulse, se realiza una acción diferente sobre el pedido (cobrar, preparar para entrega o entregar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
    $accion = $_POST['accion'] ?? '';
    if ($idPedido) {
        try {
            switch ($accion) {
                case 'cobrar':
                    PedidoSA::registrarPago($idPedido);
                    break;
                case 'preparar_entrega':
                    PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_TERMINADO);
                    break;
                case 'entregar':
                    PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_ENTREGADO);
                    break;
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    $todosLosPedidos = PedidoSA::listarTodos();
    $pedidosCamarero = CamareroHelper::formatearPedidosParaVista($todosLosPedidos);
} catch (Exception $e) {
    $error = "Error al cargar pedidos: " . $e->getMessage();
    $pedidosCamarero = [];
}

ob_start();
?>

<div class="camarero-topbar">
    <span class="camarero-brand">BISTRO FDI</span>
    <div class="camarero-user">
        <span><?= htmlspecialchars($nombreCamarero) ?></span>

        <img src="<?= h($avatarCamareroUrl) ?>"
             alt="Avatar"
             class="camarero-avatar"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

        <div class="camarero-avatar-fallback">
            <?= strtoupper(substr($nombreCamarero, 0, 1)) ?>
        </div>
    </div>
</div>

<h2 class="page-title-pad">ESTADO DE LOS PEDIDOS (CAMARERO)</h2>
<?php if (isset($error)): ?>
    <div class="alert-error-soft">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="orders-grid">
    <?php foreach ($pedidosCamarero as $p): ?>
        <div class="<?= h($p['cardClass']) ?>">
            <div class="order-card-head">
                <div class="order-card-head-top">
                    <h3 class="title-reset order-info-strong">
                        Pedido #<?= h((string) $p['numeroPedido']) ?>
                    </h3>
                    <span class="order-type-badge">
                        <?= h($p['tipoTexto']) ?>
                    </span>
                </div>
                <p class="order-time"><?= h($p['hora']) ?></p>
            </div>

            <div class="order-card-body">
                <p class="order-info">Cliente ID: <?= h((string) $p['idCliente']) ?></p>
                <p class="order-info-strong">
                    <strong><?= h($p['totalFormateado']) ?></strong>
                </p>
                <p class="order-info">
                    Estado: <strong><?= h($p['estado']) ?></strong>
                </p>

                <?php if ($p['mostrarRevision']): ?>
                    <div class="order-review-wrap">
                        <a href="<?= RUTA_VISTAS ?>/cliente/pedido_detalle.php?id=<?= h((string) $p['id']) ?>"
                           class="order-review-link">
                            🔍 REVISAR PRODUCTOS
                        </a>
                    </div>
                <?php endif; ?>

                <div class="order-actions">
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="id_pedido" value="<?= h((string) $p['id']) ?>">
                        <input type="hidden" name="accion" value="<?= h($p['accion']) ?>">
                        <button type="submit" class="<?= h($p['btnClass']) ?>">
                            <?= h($p['textoBoton']) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($pedidosCamarero)): ?>
        <div class="order-empty">
            <p class="text-large text-muted-3">No hay pedidos que gestionar.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';