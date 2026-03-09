<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';

// Manejo de borrado lógico (retirar de carta)
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

// Manejo de REOFERTAR (Recuperar producto)
$idReofertar = (int)($_GET['reofertar'] ?? 0);
if ($idReofertar > 0) {
    try {
        ProductoSA::ponerEnCarta($idReofertar); // Este método ya lo tienes en tu ProductoSA
        $app->putAtributoPeticion('msg', 'Producto vuelto a poner en carta.');
    } catch (Throwable $e) {
        $app->putAtributoPeticion('msg', 'Error: ' . $e->getMessage());
    }
    header('Location: ' . $base . '/productos_listar.php');
    exit;
}


$mensaje  = $app->getAtributoPeticion('msg');
// Listamos todos (incluso los no ofertados para que el gerente pueda reactivarlos)
$productos = ProductoSA::listar();
$categoriasMap = [];
foreach(CategoriaSA::listar() as $cat) {
    $categoriasMap[$cat->getId()] = $cat->getNombre();
}

$tituloPagina = 'Gestión de Productos';

ob_start();
?>
<section class="ger-wrap">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
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
        ?>
        <div class="card" style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap; <?= !$p->isOfertado() ? 'opacity:0.6; background:#f9f9f9;' : '' ?>">
          
          <img src="<?= h(RUTA_IMGS.'/productos/'.$imgPrincipal) ?>" alt=""
               style="width:150px; aspect-ratio:1/1; object-fit:cover; border:1px solid #ddd; border-radius:8px;">

          <div class="stack" style="flex:1; min-width:240px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <h3 style="margin:0;"><?= h($p->getNombre()) ?></h3>
                <span class="badge"><?= h($categoriasMap[$p->getIdCategoria()] ?? 'Sin categoría') ?></span>
            </div>
            
            <p class="muted" style="font-size:0.9em;"><?= h($p->getDescripcion() ?? 'Sin descripción') ?></p>
            
            <div style="display:flex; gap:20px; font-weight:bold;">
                <span>Base: <?= number_format($p->getPrecioBase(), 2) ?>€</span>
                <span>IVA: <?= $p->getIva() ?>%</span>
                <span style="color: var(--color-accent, #d32f2f);">Total: <?= number_format($p->getPrecioFinal(), 2) ?>€</span>
            </div>

            <div class="form-actions" style="margin-top:10px;">
              <a class="btn" href="<?= h($base.'/productos_editar.php?id='.$id) ?>">Editar</a>
              
              <?php if ($p->isOfertado()): ?>
                <a class="btn btn-light" 
                  style="color: #d32f2f; border-color: #d32f2f;"
                  onclick="return confirm('¿Retirar este producto de la carta? Los clientes ya no podrán verlo.')"
                  href="<?= h($base.'/productos_listar.php?retirar='.$id) ?>">
                  Retirar de carta
                </a>
              <?php else: ?>
                <a class="btn" 
                  style="background-color: #2e7d32; color: white;"
                  onclick="return confirm('¿Volver a ofrecer este producto en la carta?')"
                  href="<?= h($base.'/productos_listar.php?reofertar='.$id) ?>">
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