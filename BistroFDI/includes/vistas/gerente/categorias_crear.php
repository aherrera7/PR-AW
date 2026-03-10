<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';
$baseUsuarios = RUTA_APP . '/includes/vistas/usuarios';
$errores = [];
$nombre = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim((string)($_POST['nombre'] ?? ''));
  $descripcion = trim((string)($_POST['descripcion'] ?? ''));
  $descripcionParam = ($descripcion === '') ? null : $descripcion;

  try {
    CategoriaSA::crearConUpload($nombre, $descripcionParam, $_FILES['imagen'] ?? null);

    $app->putAtributoPeticion('msg', 'Categoría creada correctamente.');
    header('Location: ' . $base . '/categorias_listar.php');
    exit;
  } catch (Throwable $e) {
    $errores[] = $e->getMessage();
  }
}

$tituloPagina = 'Nueva categoría';

ob_start();
?>
  <section class="ger-wrap">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
      <h1>Nueva categoría</h1>
      <a class="btn btn-light" href="<?= h($baseUsuarios.'/categorias_listar.php') ?>">Volver</a>
    </div>

    <?php if (!empty($errores)): ?>
      <div class="ger-flash ger-flash--err">
        <strong>Revisa esto:</strong>
        <ul>
          <?php foreach ($errores as $er): ?>
            <li><?= h((string)$er) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <form class="stack" method="post" enctype="multipart/form-data">
        <div>
          <label for="nombre">Nombre</label>
          <input id="nombre" name="nombre" type="text" required value="<?= h($nombre) ?>">
        </div>

        <div>
          <label for="descripcion">Descripción (opcional)</label>
          <textarea id="descripcion" name="descripcion"><?= h($descripcion) ?></textarea>
        </div>

        <div>
          <label for="imagen">Imagen (opcional)</label>
          <input id="imagen" name="imagen" type="file" accept="image/*">
          <div class="muted">Se guardará en <code>/img/categorias/</code></div>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">Crear</button>
          <a class="btn btn-light" href="<?= h($base.'/categorias_listar.php') ?>">Cancelar</a>
        </div>
      </form>
    </div>
  </section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';