<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string)($_POST['accion'] ?? ''));
    $idProducto = (int)($_POST['id'] ?? 0);

    if ($idProducto > 0) {
        try {
            if ($accion === 'retirar') {
                ProductoSA::retirarDeCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto retirado de la carta correctamente.');
            } elseif ($accion === 'reofertar') {
                ProductoSA::ponerEnCarta($idProducto);
                $app->putAtributoPeticion('msg', 'Producto vuelto a poner en carta.');
            } elseif ($accion === 'borrar') {
                ProductoSA::borrar($idProducto);
                $app->putAtributoPeticion('msg', 'Producto borrado correctamente.');
            }
        } catch (Throwable $e) {
            $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
        }
    }

    header('Location: ' . $base . '/productos_listar.php');
    exit;
}

$mensaje  = $app->getAtributoPeticion('msg');
$productos = ProductoSA::listar();

$categoriasMap = [];
foreach (CategoriaSA::listar() as $cat) {
    $categoriasMap[$cat->getId()] = $cat->getNombre();
}

$tituloPagina = 'Gestión de Productos';

ob_start();
?>
<section class="ger-wrap">
  <div class="header-bar">
    <h1>Productos</h1>
    <a class="btn" href="<?= h($base.'/productos_crear.php') ?>">+ Nuevo producto</a>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="ger-flash"><?= h((string)$mensaje) ?></div>
  <?php endif; ?>

  <?php if (empty($productos)): ?>
    <p class="muted">No hay productos en el catálogo.</p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($productos as $p): ?>
        <?php
          $id = (int)$p->getId();
          $imagenes = $p->getImagenes();
          $imgPrincipal = !empty($imagenes) ? $imagenes[0] : 'productos/default_producto.jpg';
          $cardClass = $p->isOfertado() ? 'card product-card' : 'card product-card product-card-off';
        ?>
        <div class="<?= h($cardClass) ?>">
          <img
            class="product-thumb"
            src="<?= h(RUTA_IMGS . '/' . ltrim((string)$imgPrincipal, '/')) ?>"
            alt=""
          >

          <div class="stack product-body">
            <div class="product-head">
              <h3 class="product-title"><?= h($p->getNombre()) ?></h3>
              <span class="badge"><?= h($categoriasMap[$p->getIdCategoria()] ?? 'Sin categoría') ?></span>
            </div>

            <p class="muted product-desc"><?= h($p->getDescripcion() ?? 'Sin descripción') ?></p>

            <div class="product-meta">
              <span>Base: <?= number_format($p->getPrecioBase(), 2) ?>€</span>
              <span>IVA: <?= $p->getIva() ?>%</span>
              <span class="product-total">Total: <?= number_format($p->getPrecioFinal(), 2) ?>€</span>
            </div>

            <div class="form-actions mt-10">
              <a class="btn" href="<?= h($base.'/productos_editar.php?id='.$id) ?>">Editar</a>
              <?php if ($p->isOfertado()): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <input type="hidden" name="accion" value="retirar">
                  <button
                    class="btn btn-light btn-outline-danger"
                    type="submit"
                    onclick="return confirm('¿Retirar este producto de la carta? Los clientes ya no podrán verlo.')"
                  >
                    Retirar de carta
                  </button>
                  </form>
                <?php else: ?>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="accion" value="reofertar">
                    <button
                      class="btn btn-success"
                      type="submit"
                      onclick="return confirm('¿Volver a ofrecer este producto en la carta?')"
                    >
                      Reofertar producto
                    </button>
                  </form>
                <?php endif; ?>
              
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="accion" value="borrar">
                <button
                  class="btn btn-danger"
                  type="submit"
                  onclick="return confirm('¿Seguro que quieres borrar este producto? Se eliminarán también sus imágenes.')"
                >
                  Borrar
                </button>
              </form>
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