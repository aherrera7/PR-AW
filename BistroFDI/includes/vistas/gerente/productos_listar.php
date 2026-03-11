<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';

$idRetirar = (int)($_GET['retirar'] ?? 0);
if ($idRetirar > 0) {
    try {
        ProductoSA::retirarDeCarta($idRetirar);
        $app->putAtributoPeticion('msg', 'Producto retirado de la carta correctamente.');
    } catch (Throwable $e) {
        $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
    }
    header('Location: ' . $base . '/productos_listar.php');
    exit;
}

$idReofertar = (int)($_GET['reofertar'] ?? 0);
if ($idReofertar > 0) {
    try {
        ProductoSA::ponerEnCarta($idReofertar);
        $app->putAtributoPeticion('msg', 'Producto vuelto a poner en carta.');
    } catch (Throwable $e) {
        $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
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
          $imgPrincipal = !empty($imagenes) ? $imagenes[0] : 'default_producto.jpg';
          $cardClass = $p->isOfertado() ? 'card product-card' : 'card product-card product-card-off';
        ?>
        <div class="<?= h($cardClass) ?>">
          <img
            class="product-thumb"
            src="<?= h(RUTA_IMGS.'/productos/'.$imgPrincipal) ?>"
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
                <a
                  class="btn btn-light btn-outline-danger"
                  onclick="return confirm('¿Retirar este producto de la carta? Los clientes ya no podrán verlo.')"
                  href="<?= h($base.'/productos_listar.php?retirar='.$id) ?>"
                >
                  Retirar de carta
                </a>
              <?php else: ?>
                <a
                  class="btn btn-success"
                  onclick="return confirm('¿Volver a ofrecer este producto en la carta?')"
                  href="<?= h($base.'/productos_listar.php?reofertar='.$id) ?>"
                >
                  Reofertar producto
                </a>
              <?php endif; ?>
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