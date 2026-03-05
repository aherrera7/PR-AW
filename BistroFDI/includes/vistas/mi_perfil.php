<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
  header('Location: ' . RUTA_VISTAS . '/login.php');
  exit;
}

$sa = new UsuarioSA();
$usuario = $sa->getById((int)$_SESSION['usuario_id']);

if (!$usuario) {
  header('Location: ' . RUTA_VISTAS . '/logout.php');
  exit;
}

$roles = $usuario->getRoles();
$rolNombre = (count($roles) > 0 && $roles[0]->getNombre()) ? $roles[0]->getNombre() : 'cliente';

$avatar = $usuario->getAvatar();
$avatarUrl = $avatar ? (RUTA_IMGS . '/' . ltrim($avatar, '/')) : (RUTA_IMGS . '/avatares/default.jpg');

if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

$tituloPagina = 'Mi perfil';

ob_start();
?>

<section id="contenido">
  <h2>MI PERFIL</h2>

  <div class="card" style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
    <div class="stack" style="min-width:220px;">
      <img src="<?= h($avatarUrl) ?>" alt="Avatar" style="width:90px;height:90px;border-radius:50%;border:1px solid #111;object-fit:cover;background:#fff;">
      <a class="btn" href="<?= RUTA_VISTAS ?>/editar_perfil.php">Editar perfil</a>
    </div>

    <div class="stack" style="flex:1; min-width:260px;">
      <div><strong>Usuario:</strong> <?= h((string)$usuario->getNombreUsuario()) ?></div>
      <div><strong>Email:</strong> <?= h((string)$usuario->getEmail()) ?></div>
      <div><strong>Nombre:</strong> <?= h((string)$usuario->getNombre()) ?></div>
      <div><strong>Apellidos:</strong> <?= h((string)$usuario->getApellidos()) ?></div>
      <div><strong>Contraseña:</strong> ************</div>
      <div><strong>Rol:</strong> <?= h((string)$rolNombre) ?></div>
      <div><strong>BistroCoins:</strong> </div>
    </div>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';