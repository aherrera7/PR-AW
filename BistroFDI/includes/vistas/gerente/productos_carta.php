<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

$idCat = filter_input(INPUT_GET, 'id_cat', FILTER_VALIDATE_INT);
$idCat = ($idCat !== false && $idCat !== null && $idCat > 0) ? $idCat : null;

$categoria = $idCat !== null ? CategoriaSA::obtener($idCat) : null;

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
$estaLogueado = !empty($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireGerente();

    $accion = trim((string)($_POST['accion'] ?? ''));
    $idProducto = (int)($_POST['id'] ?? 0);

    if ($idProducto > 0) {
        try {
            if ($accion === 'retirar') {
                ProductoSA::retirarDeCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto retirado correctamente.');
            } elseif ($accion === 'reofertar') {
                ProductoSA::ponerEnCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto reofertado correctamente.');
            }
        } catch (Throwable $e) {
            $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
        }
    }

    $destino = 'productos_carta.php';
    if ($idCat !== null) {
        $destino .= '?id_cat=' . $idCat;
    }

    header('Location: ' . $destino);
    exit;
}

$mensaje = $app->getAtributoPeticion('msg');

$productos = ProductoSA::listar($categoria?->getId(), !$esGerente);

$tituloPagina = $categoria
    ? ('Productos: ' . $categoria->getNombre())
    : 'Todos los productos';

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($categoria ? $categoria->getNombre() : 'Todos los productos') ?></h1>

            <?php if ($categoria): ?>
                <p class="muted"><?= h($categoria->getDescripcion() ?? '') ?></p>
            <?php else: ?>
                <p class="muted">Listado completo de productos</p>
            <?php endif; ?>
        </div>

        <div class="catalog-actions">
            <?php if ($esGerente): ?>
                <a
                    class="btn"
                    href="<?= h(RUTA_APP . '/includes/vistas/gerente/productos_crear.php' . ($idCat !== null ? '?id_cat=' . $idCat : '')) ?>"
                >
                    + Nuevo Producto
                </a>
            <?php endif; ?>
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="ger-flash"><?= h((string)$mensaje) ?></div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p class="muted">No hay productos disponibles.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($productos as $p): ?>
                <?php
                    $id = (int)$p->getId();
                    $imagenes = $p->getImagenes();

                    if (empty($imagenes)) {
                        $imagenes = ['productos/default_producto.jpg'];
                    }

                    $cardClasses = 'card stack product-card2';
                    if (!$p->isOfertado()) {
                        $cardClasses .= ' product-tile-off';
                    }
                ?>

                <div class="<?= h($cardClasses) ?>">
                    <div class="product-gallery2">
                        <?php foreach ($imagenes as $index => $ruta): ?>
                            <img
                                src="<?= h(RUTA_IMGS . '/' . ltrim((string)$ruta, '/')) ?>"
                                class="img-carrusel-<?= $id ?><?= $index === 0 ? '' : ' product-gallery-img is-hidden' ?>"
                                alt=""
                            >
                        <?php endforeach; ?>

                        <?php if (count($imagenes) > 1): ?>
                            <button type="button" onclick="navImg(<?= $id ?>, -1)" class="gallery-btn prev">&#10094;</button>
                            <button type="button" onclick="navImg(<?= $id ?>, 1)" class="gallery-btn next">&#10095;</button>
                        <?php endif; ?>
                    </div>

                    <div class="stack product-content">
                        <div class="product-line">
                            <h3 class="title-reset"><?= h($p->getNombre()) ?></h3>
                            <span class="price-red"><?= number_format($p->getPrecioFinal(), 2) ?>€</span>
                        </div>

                        <p class="muted product-text">
                            <?= h($p->getDescripcion() ?? '') ?>
                        </p>

                        <div class="product-footer">
                            <?php if ($esGerente): ?>
                                <div class="form-actions catalog-actions">
                                    <a
                                        class="btn btn-light flex-1 text-center"
                                        href="<?= h(RUTA_APP . '/includes/vistas/gerente/productos_editar.php?id=' . $id) ?>"
                                    >
                                        Editar
                                    </a>

                                    <form method="post" class="flex-1">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <input
                                            type="hidden"
                                            name="accion"
                                            value="<?= $p->isOfertado() ? 'retirar' : 'reofertar' ?>"
                                        >

                                        <button
                                            class="btn <?= $p->isOfertado() ? 'btn-outline-danger' : 'btn-success' ?> w-100"
                                            type="submit"
                                            onclick="return confirm('¿Cambiar estado de este producto?')"
                                        >
                                            <?= $p->isOfertado() ? 'Retirar' : 'Reofertar' ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="qty-box">
                                    <div class="qty-picker">
                                        <button type="button" class="btn-light qty-btn" onclick="modCant(<?= $id ?>, -1)">-</button>
                                        <input type="text" id="cant-<?= $id ?>" value="1" readonly class="qty-input">
                                        <button type="button" class="btn-light qty-btn" onclick="modCant(<?= $id ?>, 1)">+</button>
                                    </div>
                                    <button type="button" class="btn" onclick="addCarrito(<?= $id ?>)">Añadir al carrito</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
function navImg(id, d) {
    const imgs = document.querySelectorAll('.img-carrusel-' + id);
    let cur = 0;

    imgs.forEach((img, i) => {
        if (!img.classList.contains('is-hidden')) {
            cur = i;
        }
    });

    if (imgs[cur]) {
        imgs[cur].classList.add('is-hidden');
    }

    imgs[(cur + d + imgs.length) % imgs.length].classList.remove('is-hidden');
}

function modCant(id, d) {
    const i = document.getElementById('cant-' + id);
    let v = parseInt(i.value, 10) + d;
    if (v >= 1) {
        i.value = v;
    }
}

function addCarrito(id) {
    const logueado = <?= json_encode($estaLogueado) ?>;
    if (!logueado) {
        alert('Debes iniciar sesión para añadir productos al carrito.');
        window.location.href = '<?= RUTA_VISTAS ?>/login.php';
        return;
    }

    const c = document.getElementById('cant-' + id).value;
    window.location.href = 'carrito_gestion.php?action=add&id=' + id + '&cant=' + c;
}
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';