<?php
/** @var object $usuario */
/** @var string $rolNombre */
/** @var string $avatarUrl */
?>

<section id="contenido">
  <div class="perfil-top">
    <h2 class="perfil-title">MI PERFIL</h2>
    <div class="perfil-coins">BistroCoins: 27</div>
  </div>

  <div class="perfil-layout">
    <div class="perfil-left">
      <img class="perfil-avatar-big" src="<?= h($avatarUrl) ?>" alt="Avatar">
      <div style="margin-top:10px;">
        <a class="perfil-btn" href="<?= RUTA_APP ?>/editar_perfil.php">Editar perfil</a>
      </div>
    </div>

    <div class="perfil-info">
      <div class="perfil-row"><span class="perfil-k">Usuario:</span> <span class="perfil-v"><?= h((string)$usuario->getNombreUsuario()) ?></span></div>
      <div class="perfil-row"><span class="perfil-k">Email:</span> <span class="perfil-v"><?= h((string)$usuario->getEmail()) ?></span></div>
      <div class="perfil-row"><span class="perfil-k">Nombre:</span> <span class="perfil-v"><?= h((string)$usuario->getNombre()) ?></span></div>
      <div class="perfil-row"><span class="perfil-k">Apellidos:</span> <span class="perfil-v"><?= h((string)$usuario->getApellidos()) ?></span></div>
      <div class="perfil-row"><span class="perfil-k">Contraseña:</span> <span class="perfil-v">************</span></div>
      <div class="perfil-row"><span class="perfil-k">Rol:</span> <span class="perfil-v"><?= h((string)$rolNombre) ?></span></div>
    </div>
  </div>
</section>