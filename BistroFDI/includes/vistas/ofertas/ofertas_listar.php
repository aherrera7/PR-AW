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

function imagenOferta(string $nombre): string {
    $mapa = [
        'Combo Burger' => 'productos/combo_hamburguesa.png',
        'Pack Refresco' => 'productos/pack_refresco.png',
        'Desayuno Andaluz' => 'productos/pack_desayuno_andaluz.png',
    ];

    return $mapa[$nombre] ?? 'productos/default_producto.jpg';
}

ob_start();
?>

<section class="ger-wrap">
    <div class="header-bar">
        <h1>🎁 Ofertas disponibles</h1>

        <a class="btn" href="<?= h(RUTA_APP . '/includes/vistas/usuarios/categorias_listar.php') ?>">
            Volver a la carta
        </a>
    </div>

    <?php if (!empty($_SESSION['mensaje_exito'])): ?>
        <div class="alert-exito">
            <?= h($_SESSION['mensaje_exito']) ?>
        </div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>

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
                    $imagen = imagenOferta($oferta->getNombre());
                ?>

                <div class="card oferta-card">
                    <a
                        href="<?= h(RUTA_APP . '/includes/vistas/ofertas/oferta_detalle.php?id=' . $oferta->getId()) ?>"
                        class="oferta-card__media-link"
                    >
                        <div class="oferta-card__media">
                            <img
                                src="<?= h(RUTA_IMGS . '/' . $imagen) ?>"
                                alt="<?= h($oferta->getNombre()) ?>"
                                class="oferta-card__img"
                            >
                        </div>
                    </a>

                    <div class="oferta-card__body">
                        <a
                            href="<?= h(RUTA_APP . '/includes/vistas/ofertas/oferta_detalle.php?id=' . $oferta->getId()) ?>"
                            class="oferta-card__title-link"
                        >
                            <h3 class="oferta-card__title"><?= h($oferta->getNombre()) ?></h3>
                        </a>

                        <p class="oferta-card__desc">
                            <?= h($oferta->getDescripcion()) ?>
                        </p>
                    </div>

                    <div class="oferta-card__side">
                        <div class="oferta-card__price">
                            <div>Precio normal: <?= number_format($precioPack, 2) ?>€</div>
                            <div><strong>Oferta: <?= number_format($precioFinal, 2) ?>€</strong></div>
                            <div class="oferta-card__save">
                                <strong>Ahorras <?= number_format($ahorro, 2) ?>€</strong>
                            </div>
                        </div>

                        <div class="oferta-card__actions">
                            <a
                                class="btn oferta-card__buy-btn"
                                href="<?= h(RUTA_APP . '/includes/vistas/ofertas/oferta_carrito.php?id=' . $oferta->getId()) ?>"
                            >
                                Comprar
                            </a>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';