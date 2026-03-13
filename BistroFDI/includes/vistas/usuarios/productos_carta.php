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

$estaLogueado = !empty($_SESSION['login']);
$productos = ProductoSA::listar($idCat, true);

$tituloPagina = 'Productos: ' . $categoria->getNombre();
ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($categoria->getNombre()) ?></h1>
            <p class="muted"><?= h($categoria->getDescripcion() ?? '') ?></p>
        </div>
        <div class="catalog-actions">
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <div class="product-grid">
        <?php foreach ($productos as $p): ?>
            <?php 
                $id = $p->getId();
                $imagenes = $p->getImagenes();
                if (empty($imagenes)) $imagenes = ['productos/default_producto.jpg'];
            ?>
            <div class="card stack product-card2">
                
                <div class="product-gallery2">
                    <?php foreach ($imagenes as $index => $ruta): ?>
                        <img
                            src="<?= h(RUTA_IMGS . '/' . ltrim((string)$ruta, '/')) ?>"
                            class="img-carrusel-<?= $id ?><?= $index === 0 ? '' : ' product-gallery-img is-hidden' ?>">
                    <?php endforeach; ?>

                    <?php if (count($imagenes) > 1): ?>
                        <button onclick="navImg(<?= $id ?>, -1)" class="gallery-btn prev">&#10094;</button>
                        <button onclick="navImg(<?= $id ?>, 1)" class="gallery-btn next">&#10095;</button>
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
                                <button class="btn-light qty-btn" onclick="modCant(<?= $id ?>, -1)">-</button>
                                <input type="text" id="cant-<?= $id ?>" value="1" readonly class="qty-input">
                                <button class="btn-light qty-btn" onclick="modCant(<?= $id ?>, 1)">+</button>
                            </div>
                            <button class="btn" onclick="addCarrito(<?= $id ?>)">Añadir al carrito</button>
                        </div>
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
    imgs.forEach((img, i) => { if (img.style.display === 'block') cur = i; });
    imgs[cur].style.display = 'none';
    imgs[(cur + d + imgs.length) % imgs.length].style.display = 'block';
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