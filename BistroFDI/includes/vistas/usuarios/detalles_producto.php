<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

$app = Aplicacion::getInstance();

// Capturar ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$producto = ($id !== false && $id !== null) ? ProductoSA::obtener($id) : null;

// Si el producto no existe o no está en carta (ofertado), volvemos a la carta
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

    <div class="card" style="max-width: 900px; margin-top: 20px; padding: 30px; border-radius: 12px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
            
            <div class="stack">
                <?php 
                $imagenes = $producto->getImagenes();
                if (empty($imagenes)) $imagenes = ['productos/default_producto.jpg'];
                ?>
                <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$imagenes[0], '/')) ?>" 
                     alt="<?= h($producto->getNombre()) ?>" 
                     style="width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                
                <?php if(count($imagenes) > 1): ?>
                    <div style="display: flex; gap: 10px; margin-top: 10px; overflow-x: auto;">
                        <?php foreach($imagenes as $img): ?>
                            <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>" 
                                 style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stack-l">
                <div>
                    <h2 style="margin-bottom: 10px; color: #333;">Descripción</h2>
                    <p style="font-size: 1.2rem; line-height: 1.8; color: #555;">
                        <?= nl2br(h($producto->getDescripcion() ?? 'Este producto no tiene una descripción detallada todavía.')) ?>
                    </p>
                </div>

                <div style="background: #fdfdfd; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1.1rem; font-weight: 500;">Precio por unidad:</span>
                        <span class="price-red" style="font-size: 2rem; font-weight: 800;">
                            <?= number_format($producto->getPrecioFinal(), 2) ?>€
                        </span>
                    </div>
                    <p class="muted" style="font-size: 0.85rem; margin-top: 5px;">* IVA incluido en el precio final.</p>
                </div>

                <div class="qty-box" style="margin-top: 20px; justify-content: flex-start; gap: 15px;">
                    <div class="qty-picker">
                        <button type="button" class="btn-light qty-btn" onclick="let i = document.getElementById('cant-p'); if(i.value > 1) i.value--">-</button>
                        <input type="text" id="cant-p" value="1" readonly class="qty-input">
                        <button type="button" class="btn-light qty-btn" onclick="document.getElementById('cant-p').value++">+</button>
                    </div>
                    <button type="button" class="btn" 
                            style="padding: 12px 30px;"
                            onclick="window.location.href='productos_carta.php'">
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