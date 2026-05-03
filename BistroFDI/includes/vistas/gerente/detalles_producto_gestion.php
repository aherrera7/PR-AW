<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

// Solo el gerente debería acceder a esta vista de gestión
requireGerente();

$app = Aplicacion::getInstance();

// 1. Capturar el ID del producto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$producto = ($id !== false && $id !== null) ? ProductoSA::obtener($id) : null;

// Si no existe el producto, redirigimos
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

    <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            
            <div class="product-gallery-preview">
                <?php 
                $imagenes = $producto->getImagenes();
                $rutaImg = !empty($imagenes) ? $imagenes[0] : 'productos/default_producto.jpg';
                ?>
                <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$rutaImg, '/')) ?>" 
                     alt="<?= h($producto->getNombre()) ?>" 
                     style="width: 100%; border-radius: 8px; object-fit: cover;">
            </div>

            <div class="stack">
                <div>
                    <label class="muted" style="font-size: 0.8rem; text-transform: uppercase;">Descripción</label>
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-top: 5px;">
                        <?= nl2br(h($producto->getDescripcion() ?? 'Sin descripción disponible.')) ?>
                    </p>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="muted" style="font-size: 0.8rem; text-transform: uppercase;">Precio Final</label>
                        <p class="price-red" style="font-size: 1.5rem; font-weight: bold;">
                            <?= number_format($producto->getPrecioFinal(), 2) ?>€
                        </p>
                    </div>
                    <div>
                        <label class="muted" style="font-size: 0.8rem; text-transform: uppercase;">Estado en Carta</label>
                        <p style="margin-top: 5px;">
                            <?php if ($producto->isOfertado()): ?>
                                <span class="badge" style="background: #e6fffa; color: #2c7a7b; padding: 4px 8px; border-radius: 4px;">Visible</span>
                            <?php else: ?>
                                <span class="badge" style="background: #fff5f5; color: #c53030; padding: 4px 8px; border-radius: 4px;">Oculto</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label class="muted" style="font-size: 0.8rem; text-transform: uppercase;">Desglose Técnico</label>
                    <ul class="muted" style="list-style: none; padding: 0; font-size: 0.9rem;">
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