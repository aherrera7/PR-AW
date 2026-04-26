<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

$idCat = filter_input(INPUT_GET, 'id_cat', FILTER_VALIDATE_INT);
$idCat = ($idCat !== false && $idCat !== null && $idCat > 0) ? $idCat : null; 

$categoriaUnica = $idCat !== null ? CategoriaSA::obtener($idCat) : null;
$estaLogueado = !empty($_SESSION['login']);

// Obtenemos productos y mapa de categorías para los nombres
$productos = ProductoSA::listar($idCat, true);
$todasCategorias = CategoriaSA::listar();
$mapaCategorias = [];
foreach ($todasCategorias as $c) {
    $mapaCategorias[$c->getId()] = $c->getNombre();
}

$productosAgrupados = [];
if ($idCat === null) {
    foreach ($productos as $p) {
        $idCatProd = $p->getIdCategoria(); 
        $catNombre = $mapaCategorias[$idCatProd] ?? 'Otros';
        $productosAgrupados[$catNombre][] = $p;
    }
} else {
    $nombreGrupo = $categoriaUnica ? $categoriaUnica->getNombre() : 'Productos';
    $productosAgrupados[$nombreGrupo] = $productos;
}

$tituloPagina = $categoriaUnica ? ('Categoría: ' . $categoriaUnica->getNombre()) : 'Nuestra Carta';

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($tituloPagina) ?></h1>
            <?php if (!$categoriaUnica): ?>
                <p class="muted">Explora todas nuestras especialidades por secciones.</p>
            <?php endif; ?>
        </div>
        <div class="catalog-actions">
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <?php if ($idCat === null): ?>
        <div class="carta-ofertas-banner">
            <div class="carta-ofertas-banner__icono" aria-hidden="true">🎁</div>
            <div class="carta-ofertas-banner__contenido">
                <h2 class="carta-ofertas-banner__titulo">Ofertas disponibles</h2>
                <p class="carta-ofertas-banner__texto">
                    Descubre nuestros packs y promociones activas antes de hacer tu pedido.
                </p>
            </div>
            <div class="carta-ofertas-banner__acciones">
                <a class="btn" href="<?= h(RUTA_VISTAS . '/ofertas/ofertas_listar.php') ?>">Mostrar ofertas</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p class="muted">No hay productos en esta sección.</p>
    <?php else: ?>
        
        <?php if ($idCat === null && count($productosAgrupados) > 1): ?>
            <nav class="category-sticky-nav">
                <?php foreach (array_keys($productosAgrupados) as $nombreCat): ?>
                    <a href="#section-<?= md5((string)$nombreCat) ?>" class="nav-link-item">
                        <?= h((string)$nombreCat) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="stack-l">
            <?php foreach ($productosAgrupados as $nombreCat => $listaProductos): ?>
                <div id="section-<?= md5((string)$nombreCat) ?>" class="category-group">
                    <h2 class="category-divider"><?= h((string)$nombreCat) ?></h2>
                    
                    <div class="product-grid">
                        <?php foreach ($listaProductos as $p): ?>
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
                                        <button type="button" data-nav-img data-id="<?= $id ?>" data-dir="-1" class="gallery-btn prev">&#10094;</button>
                                        <button type="button" data-nav-img data-id="<?= $id ?>" data-dir="1" class="gallery-btn next">&#10095;</button>
                                    <?php endif; ?>
                                </div>

                                <div class="stack product-content">
                                    <div class="product-line">
                                        <h3 class="title-reset"><?= h($p->getNombre()) ?></h3>
                                        <span class="price-red"><?= number_format($p->getPrecioFinal(), 2) ?>€</span>
                                    </div>

                                    <p class="muted product-text"><?= h($p->getDescripcion() ?? '') ?></p>

                                    <div class="product-footer">
                                        <div class="qty-box">
                                            <div class="qty-picker">
                                                <button type="button" class="btn-light qty-btn" data-cant data-id="<?= $id ?>" data-dir="-1">-</button>
                                                <input type="text" id="cant-<?= $id ?>" value="1" readonly class="qty-input">
                                                <button type="button" class="btn-light qty-btn" data-cant data-id="<?= $id ?>" data-dir="1">+</button>
                                            </div>
                                            <button type="button" class="btn" data-add data-id="<?= $id ?>" data-logueado="<?= $estaLogueado ? '1' : '0' ?>">Añadir</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script src="<?= RUTA_JS ?>/productos.js"></script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';
