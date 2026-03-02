<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$errores = [];
$nombre = '';
$descripcion = '';

function subirImagenCategoria(?array $file): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir la imagen.');
    }

    $tmp = $file['tmp_name'];
    $mime = @mime_content_type($tmp) ?: '';
    if (!in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
        throw new RuntimeException('Formato no permitido (jpg, png, webp, gif).');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'img'
    };

    $dir = RAIZ_APP . '/img/categorias';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $nombreFich = 'cat_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $nombreFich;

    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }

    return $nombreFich;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $descripcionParam = ($descripcion === '') ? null : $descripcion;

    try {
        $imagen = subirImagenCategoria($_FILES['imagen'] ?? null);
        CategoriaSA::crear($nombre, $descripcionParam, $imagen);

        $app->putAtributoPeticion('msg', 'Categoría creada correctamente.');
        header('Location: ' . RUTA_APP . '/gerente/categorias_listar.php');
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Nueva categoría';

$errHtml = '';
if ($errores) {
    $lis = '';
    foreach ($errores as $er) $lis .= '<li>'.h($er).'</li>';
    $errHtml = '<div class="bf-flash bf-flash--err"><strong>Revisa esto:</strong><ul>'.$lis.'</ul></div>';
}

$contenidoPrincipal = '
<style>
  .bf-wrap{ max-width: 900px; margin: 0 auto; }
  .bf-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin: 6px 0 18px; }
  .bf-head h1{ font-size: 30px; margin:0; letter-spacing: 1px; }
  .bf-card{ border:2px solid #111; border-radius:10px; background:#fff; padding: 16px; }
  .bf-row{ display:grid; gap: 12px; }
  .bf-label{ font-weight:800; display:block; margin-bottom:4px; }
  .bf-input, .bf-text{ width:100%; border:1px solid #111; border-radius:8px; padding:10px 12px; font-family: monospace; }
  .bf-text{ min-height: 120px; resize: vertical; }
  .bf-actions{ display:flex; gap:10px; margin-top: 12px; }
  .bf-btn{ display:inline-block; border: 1px solid #111; padding: 8px 12px; border-radius: 8px; text-decoration:none; color:#111; background:#fff; font-weight:700; cursor:pointer; }
  .bf-btn-primary{ background:#111; color:#fff; }
  .bf-flash{ border: 1px solid #111; background:#e6e6e6; padding:10px 12px; border-radius: 10px; margin-bottom: 14px; }
  .bf-flash--err{ background:#fff1f2; border-color:#b00020; }
  .bf-muted{ opacity:.75; font-size: 14px; }
</style>

<section class="bf-wrap">
  <div class="bf-head">
    <h1>Nueva categoría</h1>
    <a class="bf-btn" href="'.h(RUTA_APP.'/gerente/categorias_listar.php').'">Volver</a>
  </div>

  '.$errHtml.'

  <div class="bf-card">
    <form method="post" enctype="multipart/form-data">
      <div class="bf-row">
        <div>
          <label class="bf-label" for="nombre">Nombre</label>
          <input class="bf-input" id="nombre" name="nombre" type="text" required value="'.h($nombre).'">
        </div>

        <div>
          <label class="bf-label" for="descripcion">Descripción (opcional)</label>
          <textarea class="bf-text" id="descripcion" name="descripcion">'.h($descripcion).'</textarea>
        </div>

        <div>
          <label class="bf-label" for="imagen">Imagen (opcional)</label>
          <input class="bf-input" id="imagen" name="imagen" type="file" accept="image/*">
          <div class="bf-muted">Se guardará en <code>/img/categorias/</code></div>
        </div>
      </div>

      <div class="bf-actions">
        <button class="bf-btn bf-btn-primary" type="submit">Crear</button>
        <a class="bf-btn" href="'.h(RUTA_APP.'/gerente/categorias_listar.php').'">Cancelar</a>
      </div>
    </form>
  </div>
</section>
';

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';