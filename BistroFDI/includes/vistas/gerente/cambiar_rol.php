<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php'; 
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

$sa = new UsuarioSA();

$id = (int)($_GET['id'] ?? 0);
$usuario = $sa->getById($id);

if (!$usuario) {
  header('Location: ' . RUTA_VISTAS . '/gerente/usuarios.php');
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
    if (!empty($_SESSION['usuario_id'])
        && (int)$_SESSION['usuario_id'] === (int)$usuario->getId()
        && $nuevoRol !== 'gerente') {
      $errores[] = 'No puedes quitarte el rol gerente a ti mismo.';
    } else {
      $sa->cambiarRolUsuario((int)$usuario->getId(), $nuevoRol);
      header('Location: ' . RUTA_VISTAS . '/gerente/usuarios.php');
      exit;
    }
  }
}

$tituloPagina = 'Cambiar rol';

ob_start();
?>

<section class="ger-wrap">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
    <h1>Cambiar rol</h1>
    <a class="btn btn-light" href="<?= h(RUTA_VISTAS.'/gerente/usuarios.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>Revisa esto:</strong>
      <ul>
        <?php foreach ($errores as $e): ?>
          <li><?= h((string)$e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card stack">
    <p>Usuario: <strong>@<?= h((string)$usuario->getNombreUsuario()) ?></strong></p>

    <form method="post" class="stack">
      <div>
        <label for="rol">Rol</label>
        <select id="rol" name="rol">
          <?php foreach ($roles as $r): ?>
            <option value="<?= h((string)$r) ?>" <?= ((string)$r === (string)$rolActual) ? 'selected' : '' ?>>
              <?= h(ucfirst((string)$r)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= h(RUTA_VISTAS.'/gerente/usuarios.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';