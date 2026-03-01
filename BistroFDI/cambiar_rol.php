<?php
require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['esGerente']) || $_SESSION['esGerente'] !== true) {
  header('Location: ' . RUTA_APP . '/index.php');
  exit;
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$sa = new UsuarioSA();

$id = (int)($_GET['id'] ?? 0);
$usuario = $sa->getById($id);
if (!$usuario) {
  header('Location: ' . RUTA_APP . '/usuarios.php');
  exit;
}

$roles = ['cliente','camarero','cocinero','gerente'];
$rolActual = $usuario->getRoles()[0]->getNombre() ?? 'cliente';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nuevoRol = (string)($_POST['rol'] ?? '');

  if (!in_array($nuevoRol, $roles, true)) {
    $errores[] = 'Rol no válido.';
  } else {
    // opcional: evitar que te quites el rol gerente a ti mismo
    if (!empty($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === $usuario->getId() && $nuevoRol !== 'gerente') {
      $errores[] = 'No puedes quitarte el rol gerente a ti mismo.';
    } else {
      $sa->cambiarRolUsuario($usuario->getId(), $nuevoRol);
      header('Location: ' . RUTA_APP . '/usuarios.php');
      exit;
    }
  }
}

$options = '';
foreach ($roles as $r) {
  $sel = ($r === $rolActual) ? 'selected' : '';
  $options .= '<option value="'.h($r).'" '.$sel.'>'.h(ucfirst($r)).'</option>';
}

$erroresHtml = '';
if ($errores) {
  $li = '';
  foreach ($errores as $e) $li .= '<li>'.h($e).'</li>';
  $erroresHtml = '<ul class="errores">'.$li.'</ul>';
}

$tituloPagina = 'Cambiar rol';

$contenidoPrincipal = '
<section class="staff-panel">
  <div class="staff-title">
    <span>CAMBIAR ROL</span>
  </div>

  '.$erroresHtml.'

  <div style="font-size:20px; margin-bottom:12px;">
    Usuario: <strong>@'.h($usuario->getNombreUsuario()).'</strong>
  </div>

  <form method="post">
    <label style="display:block; margin-bottom:6px;">Rol</label>
    <select name="rol" style="padding:8px 10px; border:2px solid #111; border-radius:8px;">
      '.$options.'
    </select>

    <div style="margin-top:14px;">
      <button class="role-btn" type="submit">Guardar</button>
      <a class="role-btn" href="'.RUTA_APP.'/usuarios.php" style="margin-left:8px; text-decoration:none;">Cancelar</a>
    </div>
  </form>
</section>';

require RAIZ_APP . '/includes/vistas/common/plantilla.php';