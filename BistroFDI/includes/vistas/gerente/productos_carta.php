<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

// 1. Capturar categoría
$idCat = filter_input(INPUT_GET, 'id_cat', FILTER_VALIDATE_INT);
$idCat = ($idCat !== false && $idCat !== null && $idCat > 0) ? $idCat : null;

$categoria = $idCat !== null ? CategoriaSA::obtener($idCat) : null;
$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
$estaLogueado = !empty($_SESSION['login']);

// 2. Lógica de acciones POST (Retirar/Reofertar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireGerente();
    $accion = trim((string)($_POST['accion'] ?? ''));
    $idProducto = (int)($_POST['id'] ?? 0);
    if ($idProducto > 0) {
        try {
            if ($accion === 'retirar') {
                ProductoSA::retirarDeCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto ocultado al cliente.');
            } elseif ($accion === 'reofertar') {
                ProductoSA::ponerEnCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto visible en carta.');
            }
        } catch (Exception $e) {
            $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$mensaje = $app->getAtributoPeticion('msg');
// Listar: false para que el gerente vea TODO (ofertado o no)
$productos = ProductoSA::listar($idCat, false);

// 3. Mapa de categorías para la agrupación
$todasCategorias = CategoriaSA::listar();
$mapaCategorias = [];
foreach ($todasCategorias as $c) {
    $mapaCategorias[$c->getId()] = $c->getNombre();
}

$productosAgrupados = [];
if ($idCat === null) {
    foreach ($productos as $p) {
        $idCatProd = $p->getIdCategoria(); 
        $catNombre = $mapaCategorias[$idCatProd] ?? 'Sin Categoría';
        $productosAgrupados[$catNombre][] = $p;
    }
} else {
    $nombreGrupo = $categoria ? $categoria->getNombre() : 'Productos';
    $productosAgrupados[$nombreGrupo] = $productos;
}

$tituloPagina = $categoria ? ('Gestión: ' . $categoria->getNombre()) : 'Gestión de Carta Completa';

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($tituloPagina) ?></h1>
            <p class="muted">Panel de control de disponibilidad y precios.</p>
        </div>
        <div class="catalog-actions" style="display: flex; gap: 10px;">
            <a class="btn btn-primary" href="productos_crear.php"> Añadir Producto</a>
            <?php if ($idCat !== null): ?>
                <a class="btn" href="productos_carta.php">Ver toda la carta</a>
            <?php endif; ?>
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="ger-flash"><?= h((string)$mensaje) ?></div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p class="muted">No hay productos que mostrar.</p>
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
                                if (empty($imagenes)) $imagenes = ['productos/default_producto.jpg'];
                                $estaEnCarta = $p->isOfertado();
                            ?>

                            <div class="card stack product-card2 <?= $estaEnCarta ? '' : 'product-off-carta' ?>" 
                                 style="<?= !$estaEnCarta ? 'opacity: 0.7; border: 1px dashed #999;' : '' ?>">
                                
                                <div class="product-gallery2">
                                    <?php foreach ($imagenes as $index => $ruta): ?>
                                        <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$ruta, '/')) ?>"
                                             class="img-carrusel-<?= $id ?> product-gallery-img<?= $index === 0 ? '' : ' is-hidden' ?>" alt="">
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
                                    <p class="muted product-text"><?= h($p->getDescripcion() ?? '') ?></p>
                                    
                                    <div class="product-footer">
                                        <div class="product-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                            <a class="btn btn-light" href="productos_editar.php?id=<?= $id ?>">Editar</a>

                                            <form action="" method="post" class="inline-form" style="margin:0;">
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <?php if ($estaEnCarta): ?>
                                                    <input type="hidden" name="accion" value="retirar">
                                                    <button type="submit" class="btn btn-outline-danger">Retirar</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="accion" value="reofertar">
                                                    <button type="submit" class="btn btn-primary">Ofertar</button>
                                                <?php endif; ?>
                                            </form>
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

<script>
function navImg(id, d) {
    const imgs = document.querySelectorAll('.img-carrusel-' + id);
    let cur = 0;
    imgs.forEach((img, i) => { if (!img.classList.contains('is-hidden')) cur = i; });
    if (imgs[cur]) imgs[cur].classList.add('is-hidden');
    imgs[(cur + d + imgs.length) % imgs.length].classList.remove('is-hidden');
}
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';