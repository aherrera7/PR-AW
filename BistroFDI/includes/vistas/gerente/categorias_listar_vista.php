<?php
/** @var array $categorias */
/** @var string|null $mensaje */
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Categorías</h1>
    <a class="ger-btn ger-btn--primary" href="<?= h(RUTA_APP.'/gerente/categorias_crear.php') ?>">+ Nueva categoría</a>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="ger-flash"><?= h((string)$mensaje) ?></div>
  <?php endif; ?>

  <?php if (empty($categorias)): ?>
    <p class="ger-muted">No hay categorías todavía.</p>
  <?php else: ?>
    <div class="ger-grid">
      <?php foreach ($categorias as $c): ?>
        <?php
          $id  = (int)$c->getId();
          $img = $c->getImagen();
        ?>
        <article class="ger-card">
          <?php if ($img): ?>
            <img class="ger-card__img" src="<?= h(RUTA_IMGS.'/categorias/'.$img) ?>" alt="">
          <?php else: ?>
            <div class="ger-card__img ger-card__img--empty" aria-hidden="true"></div>
          <?php endif; ?>

          <h3 class="ger-card__title"><?= h((string)$c->getNombre()) ?></h3>
          <p class="ger-card__desc"><?= h((string)($c->getDescripcion() ?? '')) ?></p>

          <div class="ger-card__actions">
            <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_editar.php?id='.$id) ?>">Editar</a>
            <a class="ger-btn ger-btn--danger" href="<?= h(RUTA_APP.'/gerente/categorias_borrar.php?id='.$id) ?>">Borrar</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>