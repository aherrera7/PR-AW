<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

// Solo usuarios logueados pueden ver su carrito
if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = 'Mi Carrito';
$carrito = $_SESSION['carrito'] ?? [];
$productosCarrito = [];
$total = 0;

// Cargamos los datos reales de los productos que hay en la sesión
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

ob_start();
?>
<section class="ger-wrap">
    <h1>Tu Carrito</h1>

    <?php if (empty($productosCarrito)): ?>
        <div class="card stack" style="text-align: center; padding: 40px;">
            <p class="muted">Tu carrito está vacío.</p>
            <a href="categorias_listar.php" class="btn">Ver la carta</a>
        </div>
    <?php else: ?>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 300px;">
                <div class="stack">
                    <?php foreach ($productosCarrito as $item): 
                        $p = $item['obj'];
                        $imgs = $p->getImagenes();
                        $img = !empty($imgs) ? $imgs[0] : 'default_producto.jpg';
                    ?>
                        <div class="card" style="display: flex; align-items: center; gap: 15px; padding: 15px;">
                            <img src="<?= h(RUTA_IMGS.'/productos/'.$img) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            
                            <div style="flex: 1;">
                                <h3 style="margin: 0;"><?= h($p->getNombre()) ?></h3>
                                <p class="muted" style="margin: 5px 0;"><?= number_format($p->getPrecioFinal(), 2) ?>€ / ud.</p>
                            </div>

                            <div style="text-align: center; font-weight: bold;">
                                Cant: <?= $item['cantidad'] ?>
                            </div>

                            <div style="width: 80px; text-align: right; font-weight: bold;">
                                <?= number_format($item['subtotal'], 2) ?>€
                            </div>

                            <a href="carrito_gestion.php?action=remove&id=<?= $p->getId() ?>" style="color: #d32f2f; text-decoration: none; font-size: 1.2em; padding: 10px;">&times;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px;">
                <div class="card stack" style="padding: 20px; position: sticky; top: 20px;">
                    <h3>Resumen</h3>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span><?= number_format($total / 1.10, 2) ?>€</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>IVA (10%):</span>
                        <span><?= number_format($total - ($total / 1.10), 2) ?>€</span>
                    </div>
                    <hr>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em;">
                        <span>Total:</span>
                        <span style="color: #d32f2f;"><?= number_format($total, 2) ?>€</span>
                    </div>

                    <button class="btn" style="width: 100%; margin-top: 20px;" onclick="alert('Próximo paso: Procesar pedido en DB')">
                        Confirmar Pedido
                    </button>
                    <a href="carrito_gestion.php?action=clear" class="muted" style="display: block; text-align: center; margin-top: 15px; font-size: 0.9em;">Vaciar carrito</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';