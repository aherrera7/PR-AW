<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

$app = Aplicacion::getInstance();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$producto = ($id !== false && $id !== null) ? ProductoSA::obtener($id) : null;

if (!$producto || !$producto->isOfertado()) {
    header('Location: productos_carta.php');
    exit;
}

$tituloPagina = $producto->getNombre();
$estaLogueado = !empty($_SESSION['login']);

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head">
        <div>
            <h1 class="title-reset"><?= h($producto->getNombre()) ?></h1>
            <p class="muted">Conoce más sobre este plato de nuestra selección.</p>
        </div>
        <div class="catalog-actions">
            <a class="btn btn-light" href="javascript:history.back()">Volver a la carta</a>
        </div>
    </div>

    <div class="card product-detail-card">
        <div class="product-detail-grid">
            
            <div class="stack">
                <?php 
                $imagenes = $producto->getImagenes();
                if (empty($imagenes)) $imagenes = ['productos/default_producto.jpg'];
                ?>
                <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$imagenes[0], '/')) ?>" 
                     alt="<?= h($producto->getNombre()) ?>" 
                     class="product-main-img">
                
                <?php if(count($imagenes) > 1): ?>
                    <div class="product-thumbnails">
                        <?php foreach($imagenes as $img): ?>
                            <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>" 
                                 class="thumb-img">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stack-l">
                <div>
                    <h2 class="detail-title">Descripción</h2>
                    <p class="detail-description">
                        <?= nl2br(h($producto->getDescripcion() ?? 'Este producto no tiene una descripción detallada todavía.')) ?>
                    </p>
                </div>

                <div class="price-info-box">
                    <div class="price-row">
                        <span class="price-label">Precio por unidad:</span>
                        <span class="price-red price-value">
                            <?= number_format($producto->getPrecioFinal(), 2) ?>€
                        </span>
                    </div>
                    <p class="muted vat-notice">* IVA incluido en el precio final.</p>
                </div>

                <div class="qty-box actions-container">
                    <div class="qty-picker">
                        <button type="button" class="btn-light qty-btn" onclick="let i = document.getElementById('cant-p'); if(i.value > 1) i.value--">-</button>
                        <input type="text" id="cant-p" value="1" readonly class="qty-input">
                        <button type="button" class="btn-light qty-btn" onclick="document.getElementById('cant-p').value++">+</button>
                    </div>
                    <button type="button" class="btn btn-order" onclick="window.location.href='productos_carta.php'">
                        Pedir ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';