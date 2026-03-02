<?php
/** @var array $errores */
/** @var string $nombre */
/** @var string $descripcion */
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Nueva categoría</h1>
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
        <label class="ger-label" for="nombre">Nombre</label>
        <input class="ger-input" id="nombre" name="nombre" type="text" required value="<?= h($nombre) ?>">
      </div>

      <div>
        <label class="ger-label" for="descripcion">Descripción (opcional)</label>
        <textarea class="ger-textarea" id="descripcion" name="descripcion"><?= h($descripcion) ?></textarea>
      </div>

      <div>
        <label class="ger-label" for="imagen">Imagen (opcional)</label>
        <input class="ger-input" id="imagen" name="imagen" type="file" accept="image/*">
        <div class="ger-muted">Se guardará en <code>/img/categorias/</code></div>
      </div>

      <div class="ger-actions">
        <button class="ger-btn ger-btn--primary" type="submit">Crear</button>
        <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_listar.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>