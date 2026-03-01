<?php
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
$avatarUrl = $avatar ? (RUTA_IMGS . '/' . ltrim($avatar, '/')) : (RUTA_IMGS . '/avatares/default.png');

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$tituloPagina = 'Mi perfil';

$contenidoPrincipal = '
<section id="contenido">
  <div class="perfil-top">
    <h2 class="perfil-title">MI PERFIL</h2>
    <div class="perfil-coins">BistroCoins: 27</div>
  </div>

  <div class="perfil-layout">
    <div class="perfil-left">
      <img class="perfil-avatar-big" src="'.h($avatarUrl).'" alt="Avatar">
      <div style="margin-top:10px;">
        <a class="perfil-btn" href="'.RUTA_APP.'/editar_perfil.php">Editar perfil</a>
      </div>
    </div>

    <div class="perfil-info">
      <div class="perfil-row"><span class="perfil-k">Usuario:</span> <span class="perfil-v">'.h($usuario->getNombreUsuario()).'</span></div>
      <div class="perfil-row"><span class="perfil-k">Email:</span> <span class="perfil-v">'.h($usuario->getEmail()).'</span></div>
      <div class="perfil-row"><span class="perfil-k">Nombre:</span> <span class="perfil-v">'.h($usuario->getNombre()).'</span></div>
      <div class="perfil-row"><span class="perfil-k">Apellidos:</span> <span class="perfil-v">'.h($usuario->getApellidos()).'</span></div>
      <div class="perfil-row"><span class="perfil-k">Contraseña:</span> <span class="perfil-v">************</span></div>
      <div class="perfil-row"><span class="perfil-k">Rol:</span> <span class="perfil-v">'.h((string)$rolNombre).'</span></div>
    </div>
  </div>
</section>';

require RAIZ_APP . '/includes/vistas/common/plantilla.php';