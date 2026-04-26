<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/Aplicacion.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';
require_once RAIZ_APP . '/includes/app/util/usuarios_helper.php';


$app = Aplicacion::getInstance();
$sa  = new UsuarioSA();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_id'])) {
    $idBorrar = (int)$_POST['borrar_id'];

    if (!empty($_SESSION['usuario_id']) && $idBorrar === (int)$_SESSION['usuario_id']) {
        $app->putAtributoPeticion('msg', 'No puedes borrarte a ti mismo.');
    } else {
        try {
            $ok = $sa->borrarUsuario($idBorrar);

            if ($ok) {
                $app->putAtributoPeticion('msg', 'Usuario eliminado.');
            } else {
                $app->putAtributoPeticion('msg', 'No se puede eliminar el usuario porque tiene pedidos asociados o no existe.');
            }
        } catch (Throwable $e) {
            $app->putAtributoPeticion('msg', 'No se puede eliminar el usuario porque tiene datos relacionados.');
        }
    }

    header('Location: ' . RUTA_VISTAS . '/gerente/usuarios.php');
    exit;
}

$usuarios = $sa->listarUsuarios();
$flash    = $app->getAtributoPeticion('msg');

$tituloPagina = 'Usuarios';

ob_start();
?>

<?php
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

  <div class="card">
    <ul class="userlist">
      <?php foreach ($usuarios as $u): ?>
        <?php $view = usuarioViewData($u); ?>
        <?= renderUsuario($view, $iconEdit, $iconTrash) ?>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';