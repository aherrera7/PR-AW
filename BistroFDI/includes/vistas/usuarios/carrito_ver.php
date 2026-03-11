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

$tituloPagina = 'Mi Carrito';
$carrito = $_SESSION['carrito'] ?? [];
$productosCarrito = [];
$total = 0;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        if (empty($_SESSION['carrito'])) throw new InvalidArgumentException("El carrito está vacío.");
        if (empty($_SESSION['pedido_tipo'])) throw new InvalidArgumentException("Debes elegir antes si el pedido es local o para llevar.");

        $idCliente = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idCliente <= 0) throw new InvalidArgumentException("No se pudo identificar al usuario.");

        $idPedido = PedidoSA::crearDesdeCarrito(
            $idCliente,
            $_SESSION['pedido_tipo'],
            $_SESSION['carrito']
        );

        $_SESSION['ultimo_pedido'] = $idPedido;
        unset($_SESSION['carrito']);

        header("Location: categorias_listar.php");
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

ob_start();
?>
<section class="ger-wrap">
    <h1>Tu Carrito</h1>

    <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= h($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (empty($productosCarrito)): ?>
        <div class="card stack empty-state">
            <p class="muted">Tu carrito está vacío.</p>
            <a href="categorias_listar.php" class="btn">Ver la carta</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">

            <div class="cart-main">
                <div class="stack">
                    <?php foreach ($productosCarrito as $item):
                        $p = $item['obj'];
                        $imgs = $p->getImagenes();
                        $img = !empty($imgs) ? $imgs[0] : 'default_producto.jpg';
                    ?>
                        <div class="card cart-item">
                            <img class="cart-thumb" src="<?= h(RUTA_IMGS.'/productos/'.$img) ?>" alt="">

                            <div class="cart-item-info">
                                <h3 class="cart-item-title"><?= h($p->getNombre()) ?></h3>
                                <p class="muted cart-item-price"><?= number_format($p->getPrecioFinal(), 2) ?>€ / ud.</p>
                            </div>

                            <div class="cart-item-qty">
                                Cant: <?= $item['cantidad'] ?>
                            </div>

                            <div class="cart-item-subtotal">
                                <?= number_format($item['subtotal'], 2) ?>€
                            </div>

                            <a class="cart-remove" href="carrito_gestion.php?action=remove&id=<?= $p->getId() ?>">&times;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cart-side">
                <div class="card stack summary-card">
                    <h3>Resumen</h3>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><?= number_format($total / 1.10, 2) ?>€</span>
                    </div>

                    <div class="summary-row">
                        <span>IVA (10%):</span>
                        <span><?= number_format($total - ($total / 1.10), 2) ?>€</span>
                    </div>

                    <hr>

                    <div class="summary-total">
                        <span>Total:</span>
                        <span class="summary-total-value"><?= number_format($total, 2) ?>€</span>
                    </div>

                    <form method="post">
                        <button class="btn summary-submit" name="confirmar">Confirmar Pedido</button>
                    </form>

                    <a href="carrito_gestion.php?action=clear" class="muted summary-link">Vaciar carrito</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';