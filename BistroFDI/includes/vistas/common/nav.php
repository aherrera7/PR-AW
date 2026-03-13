<?php
declare(strict_types=1);

$estaLogueado = !empty($_SESSION['login']);
$nombreUsuario = $_SESSION['nombre_usuario'] ?? '';

$esCamarero = !empty($_SESSION['esCamarero']) && $_SESSION['esCamarero'] === true;
$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
$esCocinero = !empty($_SESSION['esCocinero']) && $_SESSION['esCocinero'] === true;

$avatar = trim((string)($_SESSION['avatar'] ?? ''));

if ($avatar === '') {
    $avatarPath = 'avatares/default.jpg';
} else {
    $avatarPath = str_replace('\\', '/', $avatar);
    $avatarPath = ltrim($avatarPath, '/');

    if (str_starts_with($avatarPath, 'img/')) {
        $avatarPath = substr($avatarPath, 4);
    }
    if (!str_contains($avatarPath, '/')) {
        $avatarPath = 'avatares/' . $avatarPath;
    }
}

$avatarUrl = RUTA_IMGS . '/' . $avatarPath;

$nombreUsuarioEsc = htmlspecialchars((string)$nombreUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$avatarUrlEsc = htmlspecialchars((string)$avatarUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');




$numItems = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad) {
        $numItems += $cantidad;
    }
}
?>

<nav id="menu">
  <a href="<?= RUTA_APP ?>/index.php">Inicio</a>
  <a href="<?= RUTA_VISTAS ?>/detalles.php">Detalles</a>
  <a href="<?= RUTA_VISTAS ?>/miembros.php">Miembros</a>
  <a href="<?= RUTA_VISTAS ?>/bocetos.php">Bocetos</a>
  <a href="<?= RUTA_VISTAS ?>/planificacion.php">Planificación</a>
  <a href="<?= RUTA_VISTAS ?>/contacto.php">Contacto</a>
  
  <a href="<?= RUTA_VISTAS ?>/usuarios/categorias_listar.php">Carta</a>

  <span class="nav-right">
    <?php if (!$estaLogueado): ?>
      <a class="nav-cta" href="<?= RUTA_VISTAS ?>/login.php">Login</a>
      <a class="nav-cta" href="<?= RUTA_VISTAS ?>/registrar.php">Registro</a>
    <?php else: ?>
      <a href="<?= RUTA_VISTAS ?>/usuarios/carrito_ver.php" class="nav-carrito"> 🛒
      <?php if ($numItems > 0): ?>
        <span class="nav-carrito-badge">
          <?= $numItems ?>
        </span>
      <?php endif; ?>
      </a>
      <a class="nav-user" href="<?= RUTA_VISTAS ?>/mi_perfil.php" title="Mi perfil">
        <img class="nav-avatar"
          src="<?= $avatarUrlEsc ?>"
          alt="Avatar"
          onerror="this.onerror=null;this.src='<?= h(RUTA_IMGS . '/avatares/default.jpg') ?>';">
        <span class="nav-username"><?= $nombreUsuarioEsc ?></span>
      </a>
      <a class="nav-cta" href="<?= RUTA_VISTAS ?>/logout.php">Cerrar Sesión</a>
    <?php endif; ?>
  </span>

  <button id="btnMenu" class="hamburger" type="button" aria-label="Menú">☰</button>

  <div id="desplegable">
    <?php if ($estaLogueado): ?>
        <a href="<?= RUTA_VISTAS ?>/cliente/pedidos_listar_cliente.php">Mis Pedidos</a>

        <?php if ($esCocinero || $esGerente): ?>
            <hr style="border:0; border-top:1px solid #444; margin:10px 0;">
            <strong style="display:block; margin: 5px 15px; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Cocina</strong>
            <a href="<?= RUTA_VISTAS ?>/cocinero/pedidos_listar_cocineros.php">Pedidos Cocina</a>
        <?php endif; ?>

        <?php if ($esCamarero || $esGerente): ?>
            <hr style="border:0; border-top:1px solid #444; margin:10px 0;">
            <strong style="display:block; margin: 5px 15px; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Camarero</strong>
            <a href="<?= RUTA_VISTAS ?>/camarero/camarero_pedidos.php">Pedidos Camarero</a>
        <?php endif; ?>

        <?php if ($esGerente): ?>
            <hr style="border:0; border-top:1px solid #444; margin:10px 0;">
            <strong style="display:block; margin: 5px 15px; color: #aaa; font-size: 0.8rem; text-transform: uppercase;">Gestión</strong>
            
            <a href="<?= RUTA_VISTAS ?>/gerente/categorias_listar.php?modo=1">Gestionar Carta</a>
            <a href="<?= RUTA_VISTAS ?>/gerente/usuarios.php">Usuarios</a>
            <a href="<?= RUTA_VISTAS ?>/gerente/pedidos_listar_gerente.php">Listado Pedidos</a>
        <?php endif; ?>
    <?php endif; ?>
  </div>
</nav>