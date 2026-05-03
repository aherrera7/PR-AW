<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

requireGerente();

$app = Aplicacion::getInstance();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$producto = ($id !== false && $id !== null) ? ProductoSA::obtener($id) : null;

if (!$producto) {
    header('Location: productos_carta.php');
    exit;
}

$tituloPagina = 'Detalles: ' . $producto->getNombre();

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($producto->getNombre()) ?></h1>
            <p class="muted">Información detallada del producto en el sistema.</p>
        </div>
        <div class="catalog-actions">
            <a class="btn btn-primary" href="productos_editar.php?id=<?= $id ?>">Editar Producto</a>
            <a class="btn btn-light" href="productos_carta.php">Volver a la carta</a>
        </div>
    </div>

    <div class="card ger-detail-card">
        <div class="ger-detail-grid">
            
            <div class="product-gallery-preview">
                <?php 
                $imagenes = $producto->getImagenes();
                $rutaImg = !empty($imagenes) ? $imagenes[0] : 'productos/default_producto.jpg';
                ?>
                <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$rutaImg, '/')) ?>" 
                     alt="<?= h($producto->getNombre()) ?>" 
                     class="ger-product-img">
            </div>

            <div class="stack">
                <div>
                    <label class="info-label muted">Descripción</label>
                    <p class="info-desc">
                        <?= nl2br(h($producto->getDescripcion() ?? 'Sin descripción disponible.')) ?>
                    </p>
                </div>

                <hr class="info-separator">

                <div class="info-subgrid">
                    <div>
                        <label class="info-label muted">Precio Final</label>
                        <p class="price-red" style="font-size: 1.5rem; font-weight: bold;">
                            <?= number_format($producto->getPrecioFinal(), 2) ?>€
                        </p>
                    </div>
                    <div>
                        <label class="info-label muted">Estado en Carta</label>
                        <?php if ($producto->isOfertado()): ?>
                            <span class="status-badge status-visible">Visible</span>
                        <?php else: ?>
                            <span class="status-badge status-hidden">Oculto</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label class="info-label muted">Desglose Técnico</label>
                    <ul class="tech-details-list muted">
                        <li><strong>Precio Base:</strong> <?= number_format($producto->getPrecioBase(), 2) ?>€</li>
                        <li><strong>IVA aplicado:</strong> <?= h((string)$producto->getIva()) ?>%</li>
                        <li><strong>Categoría ID:</strong> <?= h((string)$producto->getIdCategoria()) ?></li>
                        <li><strong>Tipo:</strong> <?= $producto->getEsCocina() ? 'Producto de Cocina' : 'Producto Directo/Bebida' ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';