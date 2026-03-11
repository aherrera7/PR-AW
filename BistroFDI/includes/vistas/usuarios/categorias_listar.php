<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

$baseGerente = RUTA_APP . '/includes/vistas/gerente';
$baseUsuario = RUTA_APP . '/includes/vistas/usuarios';

$mensaje    = $app->getAtributoPeticion('msg');
$categorias = CategoriaSA::listar();

$tituloPagina = $esGerente ? 'Gestión de Categorías' : 'Nuestra Carta';

ob_start();
?>
<section class="ger-wrap">
  <div class="header-bar">
    <h1><?= h($tituloPagina) ?></h1>

    <?php if ($esGerente): ?>
      <a class="btn" href="<?= h($baseGerente.'/categorias_crear.php') ?>">+ Nueva categoría</a>
    <?php endif; ?>
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

        <div class="card media">
          <?php if ($img): ?>
            <img class="media-img" src="<?= h(RUTA_IMGS.'/categorias/'.$img) ?>" alt="">
          <?php endif; ?>

          <div class="stack media-body">
            <h3 class="title-reset"><?= h((string)$c->getNombre()) ?></h3>
            <p class="muted"><?= h((string)($c->getDescripcion() ?? '')) ?></p>

            <div class="form-actions">
              <a class="btn" href="<?= h($baseUsuario.'/productos_carta.php?id_cat='.$id) ?>">Acceder</a>

              <?php if ($esGerente): ?>
                <a class="btn btn-light" href="<?= h($baseGerente.'/categorias_editar.php?id='.$id) ?>">Editar</a>

                <form class="inline-form"
                      action="<?= h($baseGerente.'/categorias_borrar.php') ?>"
                      method="post"
                      onsubmit="return confirm('¿Seguro que quieres borrar esta categoría?');">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <button type="submit" class="btn btn-light btn-danger-light">Borrar</button>
                </form>
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