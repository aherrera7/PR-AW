<?php
// includes/vistas/common/nav.php

$estaLogueado = !empty($_SESSION['login']);
$nombreUsuario = $_SESSION['nombre_usuario'] ?? '';
$avatar = $_SESSION['avatar'] ?? null;

// avatar por defecto
$avatarUrl = $avatar
    ? (RUTA_IMGS . '/' . ltrim($avatar, '/'))
    : (RUTA_IMGS . '/avatares/default.jpg');

$nombreUsuarioEsc = htmlspecialchars($nombreUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$avatarUrlEsc = htmlspecialchars($avatarUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>

<nav id="menu">
  <a href="<?= RUTA_APP ?>/index.php">Inicio</a>
  <a href="<?= RUTA_APP ?>/detalles.php">Detalles</a>
  <a href="<?= RUTA_APP ?>/miembros.php">Miembros</a>
  <a href="<?= RUTA_APP ?>/bocetos.php">Bocetos</a>
  <a href="<?= RUTA_APP ?>/planificacion.php">Planificación</a>
  <a href="<?= RUTA_APP ?>/contacto.php">Contacto</a>

  <!-- Acceso rápido visible SIEMPRE en la barra (no solo en el desplegable) -->
  <span class="nav-right">
    <?php if (!$estaLogueado): ?>
      <a class="nav-cta" href="<?= RUTA_APP ?>/login.php">Login/Register</a>
    <?php else: ?>
      <a class="nav-user" href="<?= RUTA_APP ?>/mi_perfil.php" title="Mi perfil">
        <img class="nav-avatar" src="<?= $avatarUrlEsc ?>" alt="Avatar">
        <span class="nav-username"><?= $nombreUsuarioEsc ?></span>
      </a>
      <a class="nav-cta" href="<?= RUTA_APP ?>/logout.php">Logout</a>
    <?php endif; ?>
  </span>

  <button id="btnMenu" class="hamburger" type="button" aria-label="Menú">☰</button>

  <!-- Menú desplegable (móvil / extra) -->
  <div id="desplegable">
    <a href="<?= RUTA_APP ?>/recompensas.html">Recompensas</a>
    <a href="<?= RUTA_APP ?>/pedidos.html">Pedidos</a>
  </div>
</nav>