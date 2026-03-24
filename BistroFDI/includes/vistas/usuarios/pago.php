<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

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
    $_SESSION['errores'] = ['Debes elegir antes si el pedido es para tomar aquí o para llevar.'];
    header('Location: carrito_ver.php');
    exit;
}

if ($idCliente <= 0) {
    $_SESSION['errores'] = ['No se pudo identificar al usuario.'];
    header('Location: carrito_ver.php');
    exit;
}

function validarTarjetaLuhn(string $numero): bool
{
    $numero = preg_replace('/\D+/', '', $numero);
    if ($numero === null || $numero === '' || strlen($numero) < 13 || strlen($numero) > 19) {
        return false;
    }

    $suma = 0;
    $alternar = false;

    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digito = (int)$numero[$i];

        if ($alternar) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }

        $suma += $digito;
        $alternar = !$alternar;
    }

    return $suma % 10 === 0;
}

$productosCarrito = [];
$total = 0.0;
$errores = [];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar'])) {
    try {
        $metodoPago = $_POST['metodo_pago'] ?? null;

        if (!$metodoPago || !in_array($metodoPago, ['tarjeta', 'efectivo'], true)) {
            throw new InvalidArgumentException('Debes elegir un método de pago.');
        }

        if ($metodoPago === 'tarjeta') {
            $numeroTarjeta = trim((string)($_POST['numero_tarjeta'] ?? ''));
            $titular = trim((string)($_POST['titular'] ?? ''));
            $caducidad = trim((string)($_POST['caducidad'] ?? ''));
            $cvv = trim((string)($_POST['cvv'] ?? ''));

            if ($titular === '') {
                throw new InvalidArgumentException('Debes introducir el nombre del titular.');
            }

            if (!validarTarjetaLuhn($numeroTarjeta)) {
                throw new InvalidArgumentException('El número de tarjeta no es válido.');
            }

            if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $caducidad)) {
                throw new InvalidArgumentException('La fecha de caducidad debe tener formato MM/AA.');
            }

            if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
                throw new InvalidArgumentException('El CVV no es válido.');
            }

            [$mes, $anio] = explode('/', $caducidad);
            $mes = (int)$mes;
            $anio = 2000 + (int)$anio;

            $ultimoDiaMes = (int)date('t', strtotime(sprintf('%04d-%02d-01', $anio, $mes)));
            $fechaCaducidad = DateTime::createFromFormat('Y-m-d H:i:s', sprintf('%04d-%02d-%02d 23:59:59', $anio, $mes, $ultimoDiaMes));
            $ahora = new DateTime();

            if (!$fechaCaducidad || $fechaCaducidad < $ahora) {
                throw new InvalidArgumentException('La tarjeta está caducada.');
            }

            $idPedido = PedidoSA::crearDesdeCarrito($idCliente, $tipoPedido, $carrito);

            unset($_SESSION['carrito'], $_SESSION['pedido_tipo']);
            $_SESSION['mensaje_exito'] = "¡Pedido #$idPedido realizado con éxito y pagado con tarjeta!";
            header('Location: carrito_ver.php');
            exit;
        }

        if ($metodoPago === 'efectivo') {
            $idPedido = PedidoSA::crearDesdeCarrito($idCliente, $tipoPedido, $carrito);

            unset($_SESSION['carrito'], $_SESSION['pedido_tipo']);
            $_SESSION['mensaje_exito'] = "¡Pedido #$idPedido realizado con éxito! En breve será cobrado por el camarero.";
            header('Location: carrito_ver.php');
            exit;
        }
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Pago del pedido';
ob_start();
?>

<section class="ger-wrap">
    <h1>Pago del pedido</h1>

    <?php if (!empty($errores)): ?>
        <div class="alert-error cart-error-box">
            <?php foreach ($errores as $e): ?>
                <p class="cart-error-text"><?= h((string)$e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="cart-layout">
        <div class="cart-main">
            <div class="card stack p-30">
                <h3>Resumen</h3>
                <p><strong>Tipo de pedido:</strong> <?= h($tipoPedido === 'local' ? 'Tomar aquí' : 'Para llevar') ?></p>

                <?php foreach ($productosCarrito as $item): ?>
                    <div class="summary-row">
                        <span><?= h($item['obj']->getNombre()) ?> x<?= (int)$item['cantidad'] ?></span>
                        <span><?= number_format((float)$item['subtotal'], 2) ?>€</span>
                    </div>
                <?php endforeach; ?>

                <hr>

                <div class="summary-row text-small">
                    <span>Subtotal (sin IVA):</span>
                    <span><?= number_format($total / 1.10, 2) ?>€</span>
                </div>

                <div class="summary-row text-small">
                    <span>IVA (10%):</span>
                    <span><?= number_format($total - ($total / 1.10), 2) ?>€</span>
                </div>

                <div class="summary-total">
                    <span>Total:</span>
                    <span class="summary-total-value"><?= number_format($total, 2) ?>€</span>
                </div>
            </div>
        </div>

        <div class="cart-side cart-side-wide">
            <div class="card stack p-30">
                <h3>Elige el método de pago</h3>

                <form method="post" id="form-pago">
                    <div class="stack">
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

                    <div id="bloque-tarjeta" style="display:none; margin-top:20px;">
                        <div class="stack">
                            <label>Número de tarjeta</label>
                            <input type="text" name="numero_tarjeta" maxlength="19" placeholder="1234 5678 9012 3456">

                            <label>Titular</label>
                            <input type="text" name="titular" maxlength="100" placeholder="Nombre del titular">

                            <label>Caducidad (MM/AA)</label>
                            <input type="text" name="caducidad" maxlength="5" placeholder="08/27">

                            <label>CVV</label>
                            <input type="text" name="cvv" maxlength="4" placeholder="123">
                        </div>
                    </div>

                    <div id="bloque-efectivo" style="display:none; margin-top:20px;">
                        <p>En breve será cobrado por el camarero.</p>
                    </div>

                    <button type="submit" name="pagar" class="btn summary-submit cart-confirm-btn" style="margin-top:20px;">
                        FINALIZAR PEDIDO
                    </button>
                </form>

                <a href="carrito_ver.php" class="summary-link muted">Volver al carrito</a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="metodo_pago"]');
    const bloqueTarjeta = document.getElementById('bloque-tarjeta');
    const bloqueEfectivo = document.getElementById('bloque-efectivo');

    function actualizarVistaPago() {
        const seleccionado = document.querySelector('input[name="metodo_pago"]:checked');

        bloqueTarjeta.style.display = 'none';
        bloqueEfectivo.style.display = 'none';

        if (!seleccionado) return;

        if (seleccionado.value === 'tarjeta') {
            bloqueTarjeta.style.display = 'block';
        } else if (seleccionado.value === 'efectivo') {
            bloqueEfectivo.style.display = 'block';
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', actualizarVistaPago);
    });

    actualizarVistaPago();
});
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';