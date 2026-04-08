<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$ofertas = OfertaSA::listarActivasHoy();
$tituloPagina = 'Ofertas disponibles';

ob_start();
?>

<section class="ger-wrap">
    <div class="header-bar">
        <h1>🎁 Ofertas disponibles</h1>
        <a class="btn" href="<?= h(RUTA_APP . '/includes/vistas/usuarios/categorias_listar.php') ?>">
            Volver a la carta
        </a>
    </div>

    <?php if (empty($ofertas)): ?>
        <div class="card p-30">
            <p>No hay ofertas disponibles en este momento.</p>
        </div>
    <?php else: ?>
        <div class="stack">
            <?php foreach ($ofertas as $oferta): ?>
                <?php
                    $precioPack = OfertaSA::calcularPrecioPack($oferta->getId());
                    $precioFinal = round($precioPack * (1 - $oferta->getDescuento()), 2);
                    $ahorro = round($precioPack - $precioFinal, 2);
                ?>

                <a
                    href="<?= h(RUTA_APP . '/includes/vistas/ofertas/oferta_detalle.php?id=' . $oferta->getId()) ?>"
                    class="card"
                    style="text-decoration:none; color:inherit; padding:20px;"
                >
                    <div class="summary-row">
                        <div>
                            <h3 style="margin:0 0 8px 0;"><?= h($oferta->getNombre()) ?></h3>
                            <p class="muted" style="margin:0;"><?= h($oferta->getDescripcion()) ?></p>
                        </div>

                        <div style="text-align:right;">
                            <div>Precio normal: <?= number_format($precioPack, 2) ?>€</div>
                            <div><strong>Oferta: <?= number_format($precioFinal, 2) ?>€</strong></div>
                            <div style="color:green;"><strong>Ahorras <?= number_format($ahorro, 2) ?>€</strong></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';