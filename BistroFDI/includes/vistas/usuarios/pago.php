<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$tipoPedido = $_SESSION['pedido_tipo'] ?? null;
$idCliente = (int)($_SESSION['id_usuario'] ?? $_SESSION['usuario_id'] ?? 0);

if (empty($carrito)) {
    $_SESSION['errores'] = ['El carrito está vacío.'];
    header('Location: carrito_ver.php');
    exit;
}

if (!$tipoPedido || !in_array($tipoPedido, ['local', 'llevar'], true)) {
    $_SESSION['errores'] = ['Debes elegir el tipo de pedido.'];
    header('Location: carrito_ver.php');
    exit;
}

if ($idCliente <= 0) {
    $_SESSION['errores'] = ['Usuario no válido.'];
    header('Location: carrito_ver.php');
    exit;
}

$productosCarrito = [];
$total = 0.0;
$errores = [];
$mejorOferta = null;
$descuentoOferta = 0.0;

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

$mejorOferta = OfertaSA::obtenerMejorOfertaAplicable($carrito);
if ($mejorOferta !== null) {
    $descuentoOferta = (float)($mejorOferta['descuento_total'] ?? 0);
}

$totalFinal = max(0, round($total - $descuentoOferta, 2));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar'])) {

    try {
        $metodoPago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$metodoPago || !in_array($metodoPago, ['tarjeta', 'efectivo'], true)) {
            throw new InvalidArgumentException('Debes elegir un método de pago.');
        }

        if ($metodoPago === 'tarjeta') {

            $titular = trim((string) filter_input(INPUT_POST, 'titular'));
            $numeroTarjeta = preg_replace('/\D+/', '', (string) filter_input(INPUT_POST, 'numero_tarjeta'));
            $caducidad = trim((string) filter_input(INPUT_POST, 'caducidad'));
            $cvv = trim((string) filter_input(INPUT_POST, 'cvv'));

            if ($titular === '') throw new InvalidArgumentException('Titular obligatorio.');
            if (!preg_match('/^[0-9]{16}$/', $numeroTarjeta)) throw new InvalidArgumentException('Tarjeta inválida.');
            if (!preg_match('/^[0-9]{3,4}$/', $cvv)) throw new InvalidArgumentException('CVV inválido.');

            $idPedido = PedidoSA::crearDesdeCarrito($idCliente, $tipoPedido, $carrito);
        }

        if ($metodoPago === 'efectivo') {
            $idPedido = PedidoSA::crearDesdeCarrito($idCliente, $tipoPedido, $carrito);
        }

        unset($_SESSION['carrito'], $_SESSION['pedido_tipo']);
        $_SESSION['mensaje_exito'] = "Pedido #$idPedido realizado correctamente.";

        header('Location: carrito_ver.php');
        exit;

    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Pago del pedido';
ob_start();
?>

<section class="ger-wrap">
    <h1>Pago del pedido</h1>

    <?php foreach ($errores as $e): ?>
        <p class="alert-error"><?= h($e) ?></p>
    <?php endforeach; ?>

    <div class="cart-layout">
        <div class="cart-main">
            <div class="card stack p-30">
                <h3>Resumen</h3>

                <p>
                    <strong>Tipo de pedido:</strong>
                    <?= h($tipoPedido === 'local' ? 'Tomar aquí' : 'Para llevar') ?>
                </p>

                <?php foreach ($productosCarrito as $item): ?>
                    <div class="summary-row">
                        <span><?= h($item['obj']->getNombre()) ?> x<?= (int)$item['cantidad'] ?></span>
                        <span><?= number_format((float)$item['subtotal'], 2) ?>€</span>
                    </div>
                <?php endforeach; ?>

                <hr>

                <div class="summary-row text-small">
                    <span>Subtotal (sin descuento):</span>
                    <span><?= number_format($total, 2) ?>€</span>
                </div>

                <?php if ($mejorOferta !== null): ?>
                    <div class="summary-row text-small">
                        <span>Oferta aplicada:</span>
                        <span><?= h((string)$mejorOferta['nombre_oferta']) ?></span>
                    </div>

                    <div class="summary-row text-small">
                        <span>Descuento:</span>
                        <span>-<?= number_format($descuentoOferta, 2) ?>€</span>
                    </div>
                <?php else: ?>
                    <div class="summary-row text-small">
                        <span>Oferta:</span>
                        <span>No hay ofertas aplicables</span>
                    </div>
                <?php endif; ?>

                <div class="summary-total">
                    <span>Total:</span>
                    <span class="summary-total-value"><?= number_format($totalFinal, 2) ?>€</span>
                </div>
            </div>
        </div>

        <div class="cart-side cart-side-wide">
            <div class="card stack p-30">

                <h3>Elige el método de pago</h3>

                <form method="post">

                    <div class="cart-choice-grid">

                        <label class="flex-1">
                            <input type="radio" name="metodo_pago" value="tarjeta" required class="radio-tipo cart-radio-hidden">
                            <div class="tipo-btn">
                                <span class="cart-choice-icon">💳</span><br>Tarjeta
                            </div>
                        </label>

                        <label class="flex-1">
                            <input type="radio" name="metodo_pago" value="efectivo" required class="radio-tipo cart-radio-hidden">
                            <div class="tipo-btn">
                                <span class="cart-choice-icon">💵</span><br>Efectivo
                            </div>
                        </label>

                    </div>

                    <div id="bloque-tarjeta" class="bloque-pago">
                        <div class="stack">
                            <input type="text" name="numero_tarjeta" placeholder="Tarjeta">
                            <input type="text" name="titular" placeholder="Titular">
                            <input type="text" name="caducidad" placeholder="MM/AA">
                            <input type="text" name="cvv" placeholder="CVV">
                        </div>
                    </div>

                    <div id="bloque-efectivo" class="bloque-pago">
                        <p>Pagarás en el local</p>
                    </div>

                    <button type="submit" name="pagar" class="btn summary-submit cart-confirm-btn mt-20">
                        FINALIZAR PEDIDO
                    </button>

                </form>

                <a href="carrito_ver.php" class="summary-link muted">Volver al carrito</a>

            </div>
        </div>

    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
?>

<script src="<?= RUTA_JS ?>/pago.js"></script>

<?php
require RAIZ_APP . '/includes/vistas/common/plantilla.php';