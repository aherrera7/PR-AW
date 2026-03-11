<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

$idCat = (int)($_GET['id_cat'] ?? 0);
$categoria = $idCat > 0 ? CategoriaSA::obtener($idCat) : null;

if (!$categoria) {
    header('Location: categorias_listar.php');
    exit;
}

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
$estaLogueado = !empty($_SESSION['login']);

$productos = ProductoSA::listar($idCat, !$esGerente);

$tituloPagina = 'Productos: ' . $categoria->getNombre();
ob_start();
?>

<section class="ger-wrap">
    <div class="catalog-head">
        <div>
            <h1 class="title-reset"><?= h($categoria->getNombre()) ?></h1>
            <p class="muted"><?= h($categoria->getDescripcion() ?? '') ?></p>
        </div>

        <div class="catalog-actions">
            <?php if ($esGerente): ?>
                <a class="btn" href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_crear.php?id_cat=<?= $idCat ?>">+ Nuevo Producto</a>
            <?php endif; ?>
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <div class="product-grid">
        <?php foreach ($productos as $p): ?>
            <?php
                $id = $p->getId();
                $imagenes = $p->getImagenes();
                if (empty($imagenes)) $imagenes = ['default_producto.jpg'];
                $tileClass = $p->isOfertado() ? 'card stack product-tile' : 'card stack product-tile product-tile-off';
            ?>
            <div class="<?= h($tileClass) ?>">

                <div class="product-gallery">
                    <?php foreach ($imagenes as $index => $ruta): ?>
                        <img
                          src="<?= h(RUTA_IMGS.'/productos/'.$ruta) ?>"
                          class="img-carrusel-<?= $id ?> product-gallery-img<?= $index === 0 ? '' : ' is-hidden' ?>"
                          alt=""
                        >
                    <?php endforeach; ?>

                    <?php if (count($imagenes) > 1): ?>
                        <button class="product-gallery-btn prev" onclick="navImg(<?= $id ?>, -1)">&#10094;</button>
                        <button class="product-gallery-btn next" onclick="navImg(<?= $id ?>, 1)">&#10095;</button>
                    <?php endif; ?>
                </div>

                <div class="stack product-content">
                    <div class="product-line">
                        <h3 class="product-name"><?= h($p->getNombre()) ?></h3>
                        <span class="product-price"><?= number_format($p->getPrecioFinal(), 2) ?>€</span>
                    </div>

                    <p class="muted product-text">
                        <?= h($p->getDescripcion() ?? '') ?>
                    </p>

                    <div class="product-footer">
                        <?php if ($esGerente): ?>
                            <div class="form-actions product-actions">
                                <a class="btn btn-light" href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_editar.php?id=<?= $id ?>">Editar</a>
                                <a class="btn btn-light btn-danger-light"
                                   href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_retirar.php?id=<?= $id ?>&id_cat=<?= $idCat ?>"
                                   onclick="return confirm('¿Cambiar estado de este producto?')">
                                    <?= $p->isOfertado() ? 'Retirar' : 'Reofertar' ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="qty-box">
                                <div class="qty-picker">
                                    <button class="btn-light qty-btn" onclick="modCant(<?= $id ?>, -1)">-</button>
                                    <input class="qty-input" type="number" id="cant-<?= $id ?>" value="1" min="1" readonly>
                                    <button class="btn-light qty-btn" onclick="modCant(<?= $id ?>, 1)">+</button>
                                </div>
                                <button class="btn" onclick="addCarrito(<?= $id ?>)">Añadir al carrito</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
function navImg(id, d) {
    const imgs = document.querySelectorAll('.img-carrusel-' + id);
    let cur = 0;
    imgs.forEach((img, i) => {
        if (!img.classList.contains('is-hidden')) cur = i;
    });
    imgs[cur].classList.add('is-hidden');
    imgs[(cur + d + imgs.length) % imgs.length].classList.remove('is-hidden');
}

function modCant(id, d) {
    const i = document.getElementById('cant-' + id);
    let v = parseInt(i.value) + d;
    if (v >= 1) i.value = v;
}

function addCarrito(id) {
    const logueado = <?= json_encode($estaLogueado) ?>;
    if (!logueado) {
        alert("Debes iniciar sesión para añadir productos al carrito.");
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