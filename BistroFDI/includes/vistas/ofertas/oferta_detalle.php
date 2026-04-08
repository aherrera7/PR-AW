<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

function imagenOferta(string $nombreOferta): string {
    $mapa = [
        'Combo Burger' => 'productos/combo_hamburguesa.png',
        'Pack Refresco' => 'productos/pack_refresco.png',
        'Desayuno Andaluz' => 'productos/pack_desayuno_andaluz.png',
    ];

    return $mapa[$nombreOferta] ?? 'productos/default_producto.jpg';
}

$id = (int)($_GET['id'] ?? 0);
$oferta = OfertaSA::obtener($id);

if ($id <= 0 || $oferta === null) {
    header('Location: ' . RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php');
    exit;
}

$lineas = OfertaSA::obtenerProductosOferta($id);
$precioPack = OfertaSA::calcularPrecioPack($id);
$precioFinal = round($precioPack * (1 - $oferta->getDescuento()), 2);
$ahorro = round($precioPack - $precioFinal, 2);
$rutaImagen = imagenOferta($oferta->getNombre());

$tituloPagina = 'Detalle de oferta';

ob_start();
?>

<section class="ger-wrap">
    <div class="header-bar">
        <h1>🎁 <?= h($oferta->getNombre()) ?></h1>
        <a class="btn" href="<?= h(RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php') ?>">
            Volver a ofertas
        </a>
    </div>

    <div class="card p-30">
        <div style="margin-bottom:20px;">
            <img
                src="<?= h(RUTA_IMGS . '/' . $rutaImagen) ?>"
                alt="<?= h($oferta->getNombre()) ?>"
                style="width:220px; height:220px; object-fit:cover; border-radius:10px; border:1px solid #ccc;"
            >
        </div>

        <p><?= h($oferta->getDescripcion()) ?></p>

        <div class="summary-row">
            <span><strong>Fecha inicio:</strong></span>
            <span><?= h($oferta->getFechaInicio()) ?></span>
        </div>

        <div class="summary-row">
            <span><strong>Fecha fin:</strong></span>
            <span><?= h($oferta->getFechaFin()) ?></span>
        </div>

        <hr>

        <h3>Productos incluidos</h3>

        <?php foreach ($lineas as $linea): ?>
            <?php $producto = ProductoSA::obtener($linea->getIdProducto()); ?>
            <?php if ($producto !== null): ?>
                <div class="summary-row">
                    <span><?= h($producto->getNombre()) ?></span>
                    <span>x<?= $linea->getCantidad() ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <hr>

        <div class="summary-row">
            <span>Precio normal:</span>
            <span><?= number_format($precioPack, 2) ?>€</span>
        </div>

        <div class="summary-row">
            <span>Descuento:</span>
            <span><?= number_format($oferta->getDescuento() * 100, 1) ?>%</span>
        </div>

        <div class="summary-row">
            <span>Precio oferta:</span>
            <span><strong><?= number_format($precioFinal, 2) ?>€</strong></span>
        </div>

        <div class="summary-row">
            <span>Ahorro:</span>
            <span style="color:green;"><strong><?= number_format($ahorro, 2) ?>€</strong></span>
        </div>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';