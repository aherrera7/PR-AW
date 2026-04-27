<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';
require_once RAIZ_APP . '/includes/app/util/carrito_helper.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = 'Mi Carrito';
$carrito = $_SESSION['carrito'] ?? [];
$productosCarrito = [];
$total = 0;
$errores = [];
$mejorOferta = null;
$descuentoOferta = 0.0;
$totalFinal = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        if (empty($_SESSION['carrito'])) {
            throw new InvalidArgumentException("El carrito está vacío.");
        }

        $tipoPedido = $_POST['pedido_tipo'] ?? null;
        if (!$tipoPedido || !in_array($tipoPedido, ['local', 'llevar'], true)) {
            throw new InvalidArgumentException("Debes elegir si el pedido es para tomar aquí o para llevar.");
        }

        $_SESSION['pedido_tipo'] = $tipoPedido;

        header('Location: pago.php');
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

foreach ($carrito as $id => $cantidad) {
    $p = ProductoSA::obtener((int)$id);
    if ($p) {
        $subtotal = $p->getPrecioFinal() * $cantidad;
        $total += $subtotal;
        $productosCarrito[] = [
            'obj' => $p,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }
}

if (!empty($carrito)) {
    $mejorOferta = OfertaSA::obtenerMejorOfertaAplicable($carrito);

    if ($mejorOferta !== null) {
        $descuentoOferta = (float)($mejorOferta['descuento_total'] ?? 0);
    }
}

$totalFinal = max(0, round($total - $descuentoOferta, 2));

ob_start();
?>

<section class="ger-wrap">
    <h1>Mi Carrito</h1>

    <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div class="alert-exito">
            <?= h((string)$_SESSION['mensaje_exito']) ?>
        </div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="alert-error cart-error-box">
            <?php foreach ($errores as $e): ?>
                <p class="cart-error-text"><?= h((string)$e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($productosCarrito)): ?>
        <div class="card text-center p-50">
            <p>Tu carrito está actualmente vacío.</p>
            <a href="categorias_listar.php" class="btn">Ver la carta</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">

            <div class="cart-main">
                <div class="stack cart-stack-tight">
                    <?php foreach ($productosCarrito as $item): ?>
                        <?= renderCarritoItem($item) ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cart-side cart-side-wide">
                <div class="card stack summary-card cart-summary">
                    <h3 class="mt-0">Resumen del pedido</h3>

                    <form method="post">
                        <p class="cart-choice-title">¿Cómo quieres tu pedido?</p>

                        <div class="cart-choice-grid">
                            <label class="flex-1">
                                <input type="radio" name="pedido_tipo" value="local" required class="radio-tipo cart-radio-hidden">
                                <div class="tipo-btn">
                                    <span class="cart-choice-icon">🏠</span><br>Tomar aquí
                                </div>
                            </label>

                            <label class="flex-1">
                                <input type="radio" name="pedido_tipo" value="llevar" required class="radio-tipo cart-radio-hidden">
                                <div class="tipo-btn">
                                    <span class="cart-choice-icon">🥡</span><br>Para llevar
                                </div>
                            </label>
                        </div>

                        <div class="summary-row text-small">
                            <span>Subtotal (sin descuentos):</span>
                            <span><?= number_format($total, 2) ?>€</span>
                        </div>

                        <div class="summary-row text-small">
                            <span>Subtotal (sin IVA):</span>
                            <span><?= number_format($total / 1.10, 2) ?>€</span>
                        </div>

                        <div class="summary-row text-small">
                            <span>IVA (10%):</span>
                            <span><?= number_format($total - ($total / 1.10), 2) ?>€</span>
                        </div>
                        
                        <?php if ($mejorOferta !== null): ?>
                            <div class="summary-row text-small">
                                <span>Oferta aplicada:</span>
                                <span><?= h((string)$mejorOferta['nombre_oferta']) ?></span>
                            </div>

                            <div class="summary-row text-small">
                                <span>Veces que se aplica:</span>
                                <span>x<?= h((string)$mejorOferta['veces']) ?></span>
                            </div>

                            <div class="summary-row text-small">
                                <span>Descuento:</span>
                                <span>-<?= number_format($descuentoOferta, 2) ?>€</span>
                            </div>

                            <div class="card p-20" style="margin: 15px 0; background: #f8fff3; border: 1px solid #d7ebc8;">
                                <p style="margin: 0 0 8px 0;"><strong>🎁 Oferta disponible</strong></p>
                                <p style="margin: 0 0 6px 0;"><?= h((string)$mejorOferta['nombre_oferta']) ?></p>
                                <p style="margin: 0; font-size: 0.95em;">
                                    Ahorro total: <strong><?= number_format($descuentoOferta, 2) ?>€</strong>
                                </p>
                            </div>

                        <?php else: ?>
                            <div class="summary-row text-small">
                                <span>Oferta:</span>
                                <span>No hay ofertas aplicables</span>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="summary-total">
                            <span>Total:</span>
                            <span class="summary-total-value"><?= number_format($totalFinal, 2) ?>€</span>
                        </div>

                        <button type="submit" name="confirmar" class="btn summary-submit cart-confirm-btn">
                            CONFIRMAR Y PAGAR
                        </button>
                    </form>

                    <a href="carrito_gestion.php?action=clear" class="summary-link muted cart-clear-link">Vaciar carrito</a>
                </div>
            </div>

        </div>
    <?php endif; ?>
</section>

<script src="<?= RUTA_APP ?>/js/carrito.js"></script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';