<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';
$baseUsuarios = RUTA_APP . '/includes/vistas/usuarios';

$id = (int)($_GET['id'] ?? 0);
$producto = $id > 0 ? ProductoSA::obtener($id) : null;

if (!$producto) {
    http_response_code(404);
    exit('Producto no encontrado.');
}

$errores = [];
$categorias = CategoriaSA::listar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string)$_POST['nombre']);
    $idCategoria = (int)$_POST['id_categoria'];
    $descripcion = trim((string)$_POST['descripcion']);
    $precioBase = (float)$_POST['precio_base'];
    $iva = (int)$_POST['iva'];
    $disponible = isset($_POST['disponible']);

    if (isset($_POST['borrar_fotos'])) {
        $conn = $app->getConexionBd();
        foreach ($_POST['borrar_fotos'] as $rutaImg) {
            $stmt = $conn->prepare("DELETE FROM productos_imagenes WHERE id_producto = ? AND ruta = ?");
            $stmt->bind_param('is', $id, $rutaImg);
            $stmt->execute();
            $stmt->close();
        }
    }

    $rutasNuevas = [];
    if (!empty($_FILES['imagenes']['name'][0])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                $nombreArchivo = time() . '_' . $_FILES['imagenes']['name'][$key];
                $rutaDestino = RAIZ_APP . '/img/productos/' . $nombreArchivo;
                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $rutasNuevas[] = $nombreArchivo;
                }
            }
        }
    }

    try {
        ProductoSA::actualizar($id, $idCategoria, $nombre, $descripcion, $precioBase, $iva, $disponible, $rutasNuevas);
        $app->putAtributoPeticion('msg', 'Producto actualizado con éxito.');
        header("Location:" . $baseUsuarios . "/productos_carta.php?id_cat=" . $idCategoria);
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Editar Producto';
ob_start();
?>

<section class="ger-wrap">
  <div class="header-bar">
    <h1>Editar Producto</h1>
    <a class="btn btn-light" href="<?= h($baseUsuarios.'/productos_carta.php') ?>">Volver</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>Revisa los errores:</strong>
      <ul><?php foreach ($errores as $er): ?><li><?= h($er) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <form class="stack" method="post" enctype="multipart/form-data">

      <div>
        <label>Imágenes actuales (marca para eliminar)</label>
        <div class="thumb-list">
          <?php foreach ($producto->getImagenes() as $img): ?>
            <div class="thumb-card">
              <img class="thumb-img" src="<?= h(RUTA_IMGS.'/productos/'.$img) ?>" alt="">
              <input type="checkbox" name="borrar_fotos[]" value="<?= h($img) ?>"> <small>Borrar</small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-grid">
        <div>
          <label for="nombre">Nombre</label>
          <input id="nombre" name="nombre" type="text" required value="<?= h($producto->getNombre()) ?>">
        </div>

        <div>
          <label for="id_categoria">Categoría</label>
          <select id="id_categoria" name="id_categoria" required>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat->getId() ?>" <?= $cat->getId() === $producto->getIdCategoria() ? 'selected' : '' ?>>
                <?= h($cat->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3"><?= h($producto->getDescripcion() ?? '') ?></textarea>
      </div>

      <div class="form-grid-3">
        <div>
          <label for="precio_base">Precio Base (€)</label>
          <input id="precio_base" name="precio_base" type="number" step="0.01" required value="<?= $producto->getPrecioBase() ?>">
        </div>

        <div>
          <label for="iva">IVA (%)</label>
          <select id="iva" name="iva">
            <option value="4" <?= $producto->getIva() == 4 ? 'selected' : '' ?>>4% (Superreducido)</option>
            <option value="10" <?= $producto->getIva() == 10 ? 'selected' : '' ?>>10% (Reducido)</option>
            <option value="21" <?= $producto->getIva() == 21 ? 'selected' : '' ?>>21% (General)</option>
          </select>
        </div>

        <div class="price-box">
          <small class="muted">PVP Final:</small><br>
          <strong id="precio_final" class="price-value"><?= number_format($producto->getPrecioFinal(), 2) ?>€</strong>
        </div>
      </div>

      <div>
        <label for="imagenes">Añadir nuevas imágenes</label>
        <input id="imagenes" name="imagenes[]" type="file" accept="image/*" multiple>
        <div class="muted">Puedes subir varios archivos a la vez.</div>
      </div>

      <div class="check-row">
        <input id="disponible" name="disponible" type="checkbox" <?= $producto->isDisponible() ? 'checked' : '' ?>>
        <label for="disponible">Disponible para la venta hoy</label>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit">Guardar cambios</button>
        <a class="btn btn-light" href="<?= h($baseUsuarios.'/productos_carta.php') ?>">Cancelar</a>
      </div>
    </form>
  </div>
</section>

<script>
  const pb = document.getElementById('precio_base');
  const iv = document.getElementById('iva');
  const pf = document.getElementById('precio_final');

  function actualizarPVP() {
      const base = parseFloat(pb.value) || 0;
      const iva = parseInt(iv.value) || 0;
      const total = base * (1 + (iva / 100));
      pf.textContent = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '€';
  }

  pb.addEventListener('input', actualizarPVP);
  iv.addEventListener('change', actualizarPVP);
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';