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

$estaLogueado = !empty($_SESSION['login']);
$productos = ProductoSA::listar($idCat, true);

$tituloPagina = $categoria
    ? ('Productos: ' . $categoria->getNombre())
    : 'Todos los productos';

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset">
                <?= h($categoria ? $categoria->getNombre() : 'Todos los productos') ?>
            </h1>

            <?php if ($categoria): ?>
                <p class="muted"><?= h($categoria->getDescripcion() ?? '') ?></p>
            <?php else: ?>
                <p class="muted">Listado completo de productos disponibles en la carta.</p>
            <?php endif; ?>
        </div>

        <div class="catalog-actions">
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

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
                ?>

                <div class="card stack product-card2">
                    <div class="product-gallery2">
                        <?php foreach ($imagenes as $index => $ruta): ?>
                            <img
                                src="<?= h(RUTA_IMGS . '/' . ltrim((string)$ruta, '/')) ?>"
                                class="img-carrusel-<?= $id ?> product-gallery-img<?= $index === 0 ? '' : ' is-hidden' ?>"
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
                            <div class="qty-box">
                                <div class="qty-picker">
                                    <button type="button" class="btn-light qty-btn" onclick="modCant(<?= $id ?>, -1)">-</button>
                                    <input type="text" id="cant-<?= $id ?>" value="1" readonly class="qty-input">
                                    <button type="button" class="btn-light qty-btn" onclick="modCant(<?= $id ?>, 1)">+</button>
                                </div>
                                <button type="button" class="btn" onclick="addCarrito(<?= $id ?>)">Añadir al carrito</button>
                            </div>
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