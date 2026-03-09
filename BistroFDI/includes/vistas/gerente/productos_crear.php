<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();
$base = RUTA_APP . '/includes/vistas/gerente';

$errores = [];
$categorias = CategoriaSA::listar();

// Valores por defecto para el formulario
$nombre = '';
$idCategoria = 0;
$descripcion = '';
$precioBase = 0.0;
$iva = 10; // IVA por defecto común en hostelería

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string)$_POST['nombre']);
    $idCategoria = (int)$_POST['id_categoria'];
    $descripcion = trim((string)$_POST['descripcion']);
    $precioBase = (float)$_POST['precio_base'];
    $iva = (int)$_POST['iva'];
    $disponible = isset($_POST['disponible']);
    $ofertado = true; // Al crear, por defecto se oferta

    // Gestión de subida de imágenes
    $rutasImagenes = [];
    if (!empty($_FILES['imagenes']['name'][0])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                $nombreArchivo = time() . '_' . $_FILES['imagenes']['name'][$key];
                $rutaDestino = RAIZ_APP . '/img/productos/' . $nombreArchivo;
                
                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $rutasImagenes[] = $nombreArchivo;
                }
            }
        }
    }

    try {
        ProductoSA::crear(
            $idCategoria,
            $nombre,
            $descripcion === '' ? null : $descripcion,
            $precioBase,
            $iva,
            $disponible,
            $ofertado,
            $rutasImagenes
        );

        $app->putAtributoPeticion('msg', 'Producto creado con éxito.');
        header("Location: $base/productos_listar.php");
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Nuevo Producto';
ob_start();
?>

<section class="ger-wrap">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
        <h1>Nuevo Producto</h1>
        <a class="btn btn-light" href="<?= h($base.'/productos_listar.php') ?>">Volver</a>
    </div>

    <?php if (!empty($errores)): ?>
      <div class="ger-flash ger-flash--err">
        <strong>No se pudo crear el producto:</strong>
        <ul><?php foreach ($errores as $er): ?><li><?= h($er) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card">
        <form class="stack" method="post" enctype="multipart/form-data">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label for="nombre">Nombre del Producto</label>
                    <input id="nombre" name="nombre" type="text" placeholder="Ej: Hamburguesa Gourmet" required value="<?= h($nombre) ?>">
                </div>

                <div>
                    <label for="id_categoria">Categoría</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="" disabled <?= $idCategoria === 0 ? 'selected' : '' ?>>Selecciona una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat->getId() ?>" <?= $cat->getId() === $idCategoria ? 'selected' : '' ?>>
                                <?= h($cat->getNombre()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Ingredientes, alérgenos..."><?= h($descripcion) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: end;">
                <div>
                    <label for="precio_base">Precio Base (€)</label>
                    <input id="precio_base" name="precio_base" type="number" step="0.01" required value="<?= $precioBase ?>">
                </div>

                <div>
                    <label for="iva">IVA (%)</label>
                    <select id="iva" name="iva">
                        <option value="4" <?= $iva == 4 ? 'selected' : '' ?>>4% (Superreducido)</option>
                        <option value="10" <?= $iva == 10 ? 'selected' : '' ?>>10% (Reducido)</option>
                        <option value="21" <?= $iva == 21 ? 'selected' : '' ?>>21% (General)</option>
                    </select>
                </div>

                <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px dashed #ccc; text-align: center;">
                    <small class="muted">PVP Final calculado:</small><br>
                    <strong id="precio_final" style="font-size: 1.1em; color: #d32f2f;">0,00€</strong>
                </div>
            </div>

            <div>
                <label for="imagenes">Imágenes del producto</label>
                <input id="imagenes" name="imagenes[]" type="file" accept="image/*" multiple required>
                <div class="muted">Selecciona una o varias fotos para la galería del producto.</div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <input id="disponible" name="disponible" type="checkbox" checked style="width: auto;">
                <label for="disponible" style="margin:0;">Disponible para la venta inmediatamente</label>
            </div>

            <div class="form-actions" style="margin-top: 10px;">
                <button class="btn" type="submit">Crear Producto</button>
                <a class="btn btn-light" href="<?= h($base.'/productos_listar.php') ?>">Cancelar</a>
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
    
    // Ejecutar una vez al cargar por si hay valores autocompletados
    actualizarPVP();
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';