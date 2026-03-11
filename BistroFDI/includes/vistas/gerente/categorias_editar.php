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
$nombre = (string)$categoria->getNombre();
$descripcion = (string)($categoria->getDescripcion() ?? '');
$imagenActual = $categoria->getImagen();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim((string)($_POST['nombre'] ?? ''));
  $descripcion = trim((string)($_POST['descripcion'] ?? ''));
  $descripcionParam = ($descripcion === '') ? null : $descripcion;

  try {
    CategoriaSA::actualizarConUpload($id, $nombre, $descripcionParam, $_FILES['imagen'] ?? null);

    $app->putAtributoPeticion('msg', 'Categoría actualizada.');
    header('Location: ' . $baseUsuarios . '/categorias_listar.php');
    exit;
  } catch (Throwable $e) {
    $errores[] = $e->getMessage();
  }
}

$tituloPagina = 'Editar categoría';

ob_start();
?>
<section class="ger-wrap">
  <div class="header-bar">
    <h1>Editar categoría</h1>
    <a class="btn btn-light" href="<?= h($base.'/categorias_listar.php') ?>">Volver</a>
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
        <label>Imagen actual</label>
        <?php if (!empty($imagenActual)): ?>
          <img
            class="image-box"
            src="<?= h(RUTA_IMGS.'/categorias/'.$imagenActual) ?>"
            alt=""
          >
        <?php else: ?>
          <div class="image-placeholder"></div>
        <?php endif; ?>
      </div>

      <div>
        <label for="nombre">Nombre</label>
        <input id="nombre" name="nombre" type="text" required value="<?= h($nombre) ?>">
      </div>

      <div>
        <label for="descripcion">Descripción (opcional)</label>
        <textarea id="descripcion" name="descripcion"><?= h($descripcion) ?></textarea>
      </div>

      <div>
        <label for="imagen">Cambiar imagen (opcional)</label>
        <input id="imagen" name="imagen" type="file" accept="image/*">
        <div class="muted">Si subes una nueva imagen, se reemplaza la anterior.</div>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= h($base.'/categorias_listar.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';