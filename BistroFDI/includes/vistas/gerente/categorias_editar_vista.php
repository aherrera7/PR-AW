<?php
/** @var array $errores */
/** @var int $id */
/** @var string $nombre */
/** @var string $descripcion */
/** @var string|null $imagenActual */
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Editar categoría</h1>
    <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_listar.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>Revisa esto:</strong>
      <ul>
        <?php foreach ($errores as $er): ?>
          <li><?= h((string)$er) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="ger-panel">
    <form class="ger-form" method="post" enctype="multipart/form-data">
      <div>
        <label class="ger-label">Imagen actual</label>
        <?php if (!empty($imagenActual)): ?>
          <img class="ger-preview" src="<?= h(RUTA_IMGS.'/categorias/'.$imagenActual) ?>" alt="">
        <?php else: ?>
          <div class="ger-preview ger-preview--empty" aria-hidden="true"></div>
        <?php endif; ?>
      </div>

      <div>
        <label class="ger-label" for="nombre">Nombre</label>
        <input class="ger-input" id="nombre" name="nombre" type="text" required value="<?= h($nombre) ?>">
      </div>

      <div>
        <label class="ger-label" for="descripcion">Descripción (opcional)</label>
        <textarea class="ger-textarea" id="descripcion" name="descripcion"><?= h($descripcion) ?></textarea>
      </div>

      <div>
        <label class="ger-label" for="imagen">Cambiar imagen (opcional)</label>
        <input class="ger-input" id="imagen" name="imagen" type="file" accept="image/*">
        <div class="ger-muted">Si subes una nueva imagen, se reemplaza la anterior.</div>
      </div>

      <div class="ger-actions">
        <button class="ger-btn ger-btn--primary" type="submit">Guardar</button>
        <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_listar.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>