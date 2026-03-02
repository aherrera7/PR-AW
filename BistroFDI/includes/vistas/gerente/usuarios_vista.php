<?php
/** @var array $usuarios */
/** @var string|null $flash */

$iconEdit = '<svg viewBox="0 0 24 24" fill="none"><path d="M4 20h4l10.5-10.5a2 2 0 0 0 0-3L16.5 3.5a2 2 0 0 0-3 0L3 14v6z" stroke="#111" stroke-width="2"/></svg>';
$iconTrash = '<svg viewBox="0 0 24 24" fill="none"><path d="M3 6h18" stroke="#111" stroke-width="2"/><path d="M8 6V4h8v2" stroke="#111" stroke-width="2"/><path d="M7 6l1 16h8l1-16" stroke="#111" stroke-width="2"/></svg>';
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Usuarios</h1>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="ger-flash"><?= h((string)$flash) ?></div>
  <?php endif; ?>

  <div class="ger-panel">
    <ul class="userlist">
      <?php foreach ($usuarios as $u): ?>
        <?php
          $roles = $u->getRoles();
          $rol = (count($roles) > 0) ? $roles[0]->getNombre() : 'cliente';

          $id = (int)$u->getId();
          $nombreUsuario = (string)$u->getNombreUsuario();

          $urlEditarUsuario = RUTA_APP . '/editar_perfil.php?id=' . $id; // editar_perfil se queda fuera (por ahora)
          $urlCambiarRol    = RUTA_APP . '/gerente/cambiar_rol.php?id=' . $id; // ahora en gerente
        ?>
        <li class="useritem">
          <span class="userbullet">•</span>
          <span class="userrole"><?= h(ucfirst((string)$rol)) ?>:</span>
          <span class="userhandle">@<?= h($nombreUsuario) ?></span>

          <a class="icon-btn" href="<?= h($urlEditarUsuario) ?>" title="Editar usuario"><?= $iconEdit ?></a>

          <form method="post" style="display:inline;">
            <input type="hidden" name="borrar_id" value="<?= $id ?>">
            <button class="icon-btn" type="submit" title="Eliminar"
                    onclick="return confirm('¿Eliminar este usuario?');"><?= $iconTrash ?></button>
          </form>

          <a class="ger-btn" href="<?= h($urlCambiarRol) ?>">Cambiar rol</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>