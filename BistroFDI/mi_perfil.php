<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
  header('Location: ' . RUTA_APP . '/login.php');
  exit;
}

$sa = new UsuarioSA();
$usuario = $sa->getById((int)$_SESSION['usuario_id']);

if (!$usuario) {
  header('Location: ' . RUTA_APP . '/logout.php');
  exit;
}

$roles = $usuario->getRoles();
$rolNombre = (count($roles) > 0 && $roles[0]->getNombre()) ? $roles[0]->getNombre() : 'cliente';

$avatar = $usuario->getAvatar();
$avatarUrl = $avatar ? (RUTA_IMGS . '/' . ltrim($avatar, '/')) : (RUTA_IMGS . '/avatares/default.jpg');

$tituloPagina = 'Mi perfil';

ob_start();
require RAIZ_APP . '/includes/vistas/mi_perfil_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla.php';