<?php
function renderCarritoItem(array $item): string {
    $prod = $item['obj'] ?? null;
    if (!$prod) return '';

    $imagenes = $prod->getImagenes();
    $img = (!empty($imagenes) && isset($imagenes[0])) ? $imagenes[0] : 'productos/default_producto.jpg';

    $cantidad = (int) ($item['cantidad'] ?? 0);
    $subtotal = (float) ($item['subtotal'] ?? 0);
    $idProducto = $prod->getId();

    ob_start();
    ?>
    <div class="card cart-item2" style="display: flex; align-items: center; padding: 15px; gap: 15px;">
        <img
            src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>"
            class="cart-thumb2"
            alt="<?= h((string)$prod->getNombre()) ?>"
        >

        <div class="cart-item-info" style="min-width: 150px;">
            <h4 class="title-reset" style="margin: 0 0 4px 0; font-size: 1.1em; color: #333;">
                <?= h((string)$prod->getNombre()) ?>
            </h4>
            <small class="muted" style="color: #666;">
                <?= number_format($prod->getPrecioFinal(), 2) ?>€ / ud.
            </small>
        </div>

        <div class="cart-item-qty" style="margin-left: auto; display: flex; align-items: center; background: #f1f3f5; padding: 4px; border-radius: 8px; border: 1px solid #dee2e6;">
            
            <button type="button" class="btn-qty" onclick="cambiarCantidad(<?= $idProducto ?>, -1)" 
                style="width: 30px; height: 30px; border-radius: 6px; border: 1px solid #ced4da; background: white; cursor: pointer; color: #333; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center; line-height: 1;">
                &minus;
            </button>
            
            <input type="text" 
                   id="qty-<?= $idProducto ?>" 
                   value="<?= $cantidad ?>" 
                   readonly 
                   style="width: 35px; text-align: center; border: none; background: transparent; color: #212529; font-weight: 700; font-size: 15px; padding: 0; margin: 0;">
            
            <button type="button" class="btn-qty" onclick="cambiarCantidad(<?= $idProducto ?>, 1)" 
                style="width: 30px; height: 30px; border-radius: 6px; border: 1px solid #ced4da; background: white; cursor: pointer; color: #333; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center; line-height: 1;">
                &plus;
            </button>
        </div>

        <div class="cart-item-subtotal" style="font-weight: 800; min-width: 90px; text-align: right; font-size: 1.1em; color: #222;">
            <?= number_format($subtotal, 2) ?>€
        </div>

        <a href="carrito_gestion.php?action=remove&id=<?= $idProducto ?>" 
           style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #fff5f5; color: #fa5252; border-radius: 8px; text-decoration: none; border: 1px solid #ffe3e3; transition: all 0.2s; font-size: 22px; font-weight: bold; line-height: 1; padding-bottom: 2px;"
           onmouseover="this.style.background='#ffecec'; this.style.color='#e03131';" 
           onmouseout="this.style.background='#fff5f5'; this.style.color='#fa5252';"
           title="Quitar del carrito">
            &times;
        </a>
    </div>
    <?php
    return ob_get_clean();
}