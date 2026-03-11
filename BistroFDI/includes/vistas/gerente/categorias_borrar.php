<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';
$baseUsuarios = RUTA_APP . '/includes/vistas/usuarios';

$id = (int)($_GET['id'] ?? 0);
$categoria = $id > 0 ? CategoriaSA::obtener($id) : null;
if (!$categoria) { http_response_code(404); exit('Categoría no encontrada.'); }

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    CategoriaSA::borrar($id);

    $app->putAtributoPeticion('msg', 'Categoría borrada.');
    header('Location: ' . $baseUsuarios . '/categorias_listar.php');
    exit;
  } catch (Throwable $e) {
    $errores[] = $e->getMessage();
  }
}

$tituloPagina = 'Borrar categoría';

ob_start();
?>

<section class="ger-wrap">

  <div class="header-bar">
    <h1>Borrar categoría</h1>
    <a class="btn btn-light" href="<?= h($base.'/categorias_listar.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>No se pudo borrar:</strong>
      <ul>
        <?php foreach ($errores as $er): ?>
          <li><?= h((string)$er) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="muted">Si tiene productos asignados, primero reasigna o retira esos productos.</div>
    </div>
  <?php endif; ?>

  <div class="card stack">
    <p>Vas a borrar la categoría:</p>
    <p><strong><?= h((string)$categoria->getNombre()) ?></strong></p>
    <p class="muted">Esta acción no se puede deshacer.</p>

    <form method="post">
      <div class="form-actions">
        <button class="btn btn-danger" type="submit"
          onclick="return confirm('¿Seguro que quieres borrar esta categoría?');">
          Sí, borrar
        </button>

        <a class="btn btn-light" href="<?= h($base.'/categorias_listar.php') ?>">
          Cancelar
        </a>
      </div>
    </form>
  </div>

</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';