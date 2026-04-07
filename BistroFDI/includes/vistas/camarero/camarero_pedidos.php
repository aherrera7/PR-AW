<?php
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/util/camareros_helper.php';

// Verificación de acceso (Camarero o Gerente)
requireGerenteOCamarero();

$nombreCamarero = $_SESSION['nombre'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'avatares/default.jpg';
$avatarCamareroUrl = RUTA_IMGS . '/' . ltrim((string)$avatarCamarero, '/');

$tituloPagina = "Estado de los pedidos (Camarero)";

// Procesar acciones POST
$error = CamarerosHelper::procesarAccionPost();
if ($error === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Cargar pedidos usando el helper
try {
    $todosLosPedidos = PedidoSA::listarTodos();
    $pedidosCamarero = CamarerosHelper::formatearPedidosParaVista($todosLosPedidos);
} catch (Exception $e) {
    $error = $e->getMessage();
    $pedidosCamarero = [];
}

ob_start();
?>

<div class="camarero-topbar">
    <span class="camarero-brand">BISTRO FDI</span>
    <div class="camarero-user">
        <span><?= htmlspecialchars($nombreCamarero) ?></span>
        <img src="<?= htmlspecialchars($avatarCamareroUrl) ?>"
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
        <div class="<?= $p['cardClass'] ?>">
            <div class="order-card-head">
                <div class="order-card-head-top">
                    <h3 class="title-reset order-info-strong">
                        Pedido #<?= htmlspecialchars((string) $p['numeroPedido']) ?>
                        <?php if ($p['soloBebidas']): ?>
                            <span class="solo-bebidas-badge">🥤 Solo bebidas</span>
                        <?php endif; ?>
                    </h3>
                    <span class="order-type-badge">
                        <?= htmlspecialchars($p['tipoTexto']) ?>
                    </span>
                </div>
                <p class="order-time"><?= htmlspecialchars($p['hora']) ?></p>
            </div>

            <div class="order-card-body">
                <p class="order-info">Cliente ID: <?= htmlspecialchars((string) $p['idCliente']) ?></p>
                <p class="order-info-strong">
                    <strong><?= htmlspecialchars($p['totalFormateado']) ?></strong>
                </p>
                <p class="order-info">
                    Estado: <strong><?= htmlspecialchars($p['estado']) ?></strong>
                </p>

                <div class="order-actions">
                    <?php if ($p['estado'] === 'recibido'): ?>
                        <!-- COBRAR - formulario POST -->
                        <form method="POST" action="" class="inline-form" style="width: 100%;">
                            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $p['id']) ?>">
                            <input type="hidden" name="accion" value="cobrar">
                            <button type="submit" class="btn w-100 text-center">
                                <?= $p['textoBoton'] ?>
                            </button>
                        </form>
                        
                    <?php elseif ($p['estado'] === 'listo cocina'): ?>
                        <!-- PREPARAR BEBIDAS - solo cuando la comida está lista -->
                        <a href="productos_pedido_camarero.php?id_pedido=<?= htmlspecialchars((string) $p['id']) ?>" class="btn w-100 text-center">
                            <?= $p['textoBoton'] ?>
                        </a>
                        
                    <?php elseif ($p['estado'] === 'terminado'): ?>
                        <!-- ENTREGAR -->
                        <form method="POST" action="" class="inline-form" style="width: 100%;">
                            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $p['id']) ?>">
                            <input type="hidden" name="accion" value="entregar">
                            <button type="submit" class="btn w-100 text-center">
                                <?= $p['textoBoton'] ?>
                            </button>
                        </form>
                    <?php endif; ?>
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