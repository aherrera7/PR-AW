<?php
/** @var object $usuario */
/** @var array $roles */
/** @var string $rolActual */
/** @var array $errores */
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Cambiar rol</h1>
    <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/usuarios.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>Revisa esto:</strong>
      <ul>
        <?php foreach ($errores as $e): ?>
          <li><?= h((string)$e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="ger-panel">
    <p>Usuario: <strong>@<?= h((string)$usuario->getNombreUsuario()) ?></strong></p>

    <form method="post" class="ger-form">
      <div>
        <label class="ger-label" for="rol">Rol</label>
        <select id="rol" name="rol" class="ger-input">
          <?php foreach ($roles as $r): ?>
            <option value="<?= h((string)$r) ?>" <?= ((string)$r === (string)$rolActual) ? 'selected' : '' ?>>
              <?= h(ucfirst((string)$r)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="ger-actions">
        <button class="ger-btn ger-btn--primary" type="submit">Guardar</button>
        <a class="ger-btn" href="<?= h(RUTA_APP.'/gerente/usuarios.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>