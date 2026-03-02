<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/Aplicacion.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['esGerente']) || $_SESSION['esGerente'] !== true) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$app = Aplicacion::getInstance();
$sa = new UsuarioSA();

// POST: borrar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_id'])) {
    $idBorrar = (int)$_POST['borrar_id'];

    // evitar que el gerente se borre a sí mismo
    if (!empty($_SESSION['usuario_id']) && $idBorrar === (int)$_SESSION['usuario_id']) {
        $app->putAtributoPeticion('msg', 'No puedes borrarte a ti mismo.');
    } else {
        $sa->borrarUsuario($idBorrar);
        $app->putAtributoPeticion('msg', 'Usuario eliminado.');
    }

    header('Location: ' . RUTA_APP . '/usuarios.php');
    exit;
}

$usuarios = $sa->listarUsuarios();
$tituloPagina = 'Usuarios (Gerente)';

$rows = '';

foreach ($usuarios as $u) {
    $roles = $u->getRoles();
    $rol = (count($roles) > 0) ? $roles[0]->getNombre() : 'cliente';

    $id = (int)$u->getId();
    $nombreUsuario = $u->getNombreUsuario();

    $iconEdit = '<svg viewBox="0 0 24 24" fill="none"><path d="M4 20h4l10.5-10.5a2 2 0 0 0 0-3L16.5 3.5a2 2 0 0 0-3 0L3 14v6z" stroke="#111" stroke-width="2"/></svg>';
    $iconTrash = '<svg viewBox="0 0 24 24" fill="none"><path d="M3 6h18" stroke="#111" stroke-width="2"/><path d="M8 6V4h8v2" stroke="#111" stroke-width="2"/><path d="M7 6l1 16h8l1-16" stroke="#111" stroke-width="2"/></svg>';

    $urlEditarUsuario = RUTA_APP . '/editar_perfil.php?id=' . $id;
    $urlCambiarRol    = RUTA_APP . '/cambiar_rol.php?id=' . $id;

    $rows .= '
      <li class="useritem">
        <span class="userbullet">•</span>
        <span class="userrole">'.h(ucfirst($rol)).':</span>
        <span class="userhandle">@'.h($nombreUsuario).'</span>

        <a class="icon-btn" href="'.h($urlEditarUsuario).'" title="Editar usuario">'.$iconEdit.'</a>

        <form method="post" style="display:inline;">
          <input type="hidden" name="borrar_id" value="'.$id.'">
          <button class="icon-btn" type="submit" title="Eliminar"
                  onclick="return confirm(\'¿Eliminar este usuario?\');">'.$iconTrash.'</button>
        </form>

        <a class="role-btn" href="'.h($urlCambiarRol).'">Cambiar Rol</a>
      </li>
    ';
}

$flash = $app->getAtributoPeticion('msg');
$flashHtml = $flash
    ? '<p style="margin-bottom:14px; padding:10px; border:1px solid #111; background:#e6e6e6;">'.h((string)$flash).'</p>'
    : '';

$contenidoPrincipal = $flashHtml . '
<section class="staff-panel">
  <div class="staff-title">
    <span>LISTA DE USUARIOS ACTUALES (GERENTE)</span>
  </div>

  <ul class="userlist">
    '.$rows.'
  </ul>
</section>';

require RAIZ_APP . '/includes/vistas/common/plantilla.php';