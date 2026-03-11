<?php
declare(strict_types=1);

$estaLogueado = !empty($_SESSION['login']);
$nombreUsuario = $_SESSION['nombre_usuario'] ?? '';
$avatar = $_SESSION['avatar'] ?? null;

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

$avatarUrl = $avatar
    ? (RUTA_IMGS . '/' . ltrim((string)$avatar, '/'))
    : (RUTA_IMGS . '/avatares/default.jpg');

$nombreUsuarioEsc = htmlspecialchars((string)$nombreUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$avatarUrlEsc = htmlspecialchars((string)$avatarUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>

<nav id="menu">
  <a href="<?= RUTA_APP ?>/index.php">Inicio</a>
  <a href="<?= RUTA_VISTAS ?>/detalles.php">Detalles</a>
  <a href="<?= RUTA_VISTAS ?>/miembros.php">Miembros</a>
  <a href="<?= RUTA_VISTAS ?>/bocetos.php">Bocetos</a>
  <a href="<?= RUTA_VISTAS ?>/planificacion.php">Planificación</a>
  <a href="<?= RUTA_VISTAS ?>/contacto.php">Contacto</a>
  <a href="<?= RUTA_VISTAS ?>/usuarios/pedido_tipo.php">Ver Carta</a>
  <a href="<?= RUTA_VISTAS ?>/cocinero/productos_pedido.php?id_pedido=123">
    Probar Vista Cocina (Pedido #123)
  </a>

  <span class="nav-right">
    <?php if (!$estaLogueado): ?>
      <a class="nav-cta" href="<?= RUTA_VISTAS ?>/login.php">Login/Register</a>
    <?php else: ?>

      <?php
        $numItems = 0;
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $cantidad) {
                $numItems += $cantidad;
            }
        }
      ?>

      <a href="<?= RUTA_APP ?>/includes/vistas/usuarios/carrito_ver.php" class="nav-carrito">
        🛒
        <?php if ($numItems > 0): ?>
          <span class="nav-carrito-badge"><?= $numItems ?></span>
        <?php endif; ?>
      </a>

      <a class="nav-user" href="<?= RUTA_VISTAS ?>/mi_perfil.php" title="Mi perfil">
        <img class="nav-avatar" src="<?= $avatarUrlEsc ?>" alt="Avatar">
        <span class="nav-username"><?= $nombreUsuarioEsc ?></span>
      </a>

      <a class="nav-cta" href="<?= RUTA_VISTAS ?>/logout.php">Logout</a>
    <?php endif; ?>
  </span>

  <button id="btnMenu" class="hamburger" type="button" aria-label="Menú">☰</button>

  <div id="desplegable">
    <a href="<?= RUTA_APP ?>/recompensas.html">Recompensas</a>
    <a href="<?= RUTA_APP ?>/includes/vistas/usuarios/mis_pedidos.php">Mis pedidos</a>


    <?php if ($estaLogueado && $esGerente): ?>
      <hr class="menu-divider">
      <strong class="menu-title">Gestión</strong>
      <a href="<?= RUTA_VISTAS ?>/usuarios/categorias_listar.php">Categorías</a>
      <a href="<?= RUTA_VISTAS ?>/gerente/productos_listar.php">Productos</a>
      <a href="<?= RUTA_VISTAS ?>/gerente/usuarios.php">Usuarios</a>
    <?php endif; ?>
  </div>
</nav>