<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';

$mensaje    = $app->getAtributoPeticion('msg');
$categorias = CategoriaSA::listar();

$tituloPagina = 'Gestión de Categorías';

ob_start();
?>
<section class="ger-wrap">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
    <h1>Categorías</h1>
    <a class="btn" href="<?= h($base.'/categorias_crear.php') ?>">+ Nueva categoría</a>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="ger-flash"><?= h((string)$mensaje) ?></div>
  <?php endif; ?>

  <?php if (empty($categorias)): ?>
    <p class="muted">No hay categorías todavía.</p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($categorias as $c): ?>
        <?php $id = (int)$c->getId(); $img = $c->getImagen(); ?>
        <div class="card" style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;">
          <?php if ($img): ?>
            <img src="<?= h(RUTA_IMGS.'/categorias/'.$img) ?>" alt=""
                 style="width:220px;max-width:100%;aspect-ratio:4/3;object-fit:cover;border:1px solid #111;border-radius:10px;background:#fff;">
          <?php endif; ?>

          <div class="stack" style="flex:1; min-width:240px;">
            <h3 style="margin:0;"><?= h((string)$c->getNombre()) ?></h3>
            <p class="muted"><?= h((string)($c->getDescripcion() ?? '')) ?></p>

            <div class="form-actions">
              <a class="btn" href="<?= h($base.'/categorias_editar.php?id='.$id) ?>">Editar</a>
              <a class="btn btn-danger" href="<?= h($base.'/categorias_borrar.php?id='.$id) ?>">Borrar</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';