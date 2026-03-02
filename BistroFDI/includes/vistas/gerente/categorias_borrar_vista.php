<?php
/** @var array $errores */
/** @var object $categoria */
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Borrar categoría</h1>
    <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_listar.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>No se pudo borrar:</strong>
      <ul>
        <?php foreach ($errores as $er): ?>
          <li><?= h((string)$er) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="ger-muted">Si tiene productos asignados, primero reasigna o retira esos productos.</div>
    </div>
  <?php endif; ?>

  <div class="ger-panel">
    <p>Vas a borrar la categoría:</p>
    <p><strong><?= h((string)$categoria->getNombre()) ?></strong></p>
    <p class="ger-muted">Esta acción no se puede deshacer.</p>

    <form method="post">
      <div class="ger-actions">
        <button class="ger-btn ger-btn--danger" type="submit" onclick="return confirm('¿Seguro que quieres borrar esta categoría?');">
          Sí, borrar
        </button>
        <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/categorias_listar.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>