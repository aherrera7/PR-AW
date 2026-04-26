<?php
function renderCarritoItem(array $item): string {

    $prod = $item['obj'] ?? null;

    if (!$prod) {
        return '';
    }

    $imagenes = $prod->getImagenes();
    $img = (!empty($imagenes) && isset($imagenes[0]))
        ? $imagenes[0]
        : 'productos/default_producto.jpg';

    $cantidad = (int) ($item['cantidad'] ?? 0);
    $subtotal = (float) ($item['subtotal'] ?? 0);

    ob_start();
    ?>
    <div class="card cart-item2">
        <img
            src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>"
            class="cart-thumb2"
            alt="<?= h((string)$prod->getNombre()) ?>"
        >

        <div class="flex-1">
            <h4 class="title-reset"><?= h((string)$prod->getNombre()) ?></h4>
            <small class="muted"><?= number_format($prod->getPrecioFinal(), 2) ?>€ / ud.</small>
        </div>

        <div class="cart-item-qty">
            <span>x<?= $cantidad ?></span>
        </div>

        <div class="cart-item-subtotal cart-item-subtotal-wide">
            <?= number_format($subtotal, 2) ?>€
        </div>

        <a href="carrito_gestion.php?action=remove&id=<?= (int)$prod->getId() ?>" class="cart-remove">✕</a>
    </div>
    <?php

    return ob_get_clean();
}