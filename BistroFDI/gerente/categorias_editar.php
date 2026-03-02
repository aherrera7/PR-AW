<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

$id = (int)($_GET['id'] ?? 0);
$categoria = $id > 0 ? CategoriaSA::obtener($id) : null;
if (!$categoria) { http_response_code(404); exit('Categoría no encontrada.'); }

$errores = [];
$nombre = (string)$categoria->getNombre();
$descripcion = (string)($categoria->getDescripcion() ?? '');
$imagenActual = $categoria->getImagen();

function subirImagenCategoria(?array $file): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir la imagen.');

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

    if (!move_uploaded_file($tmp, $dest)) throw new RuntimeException('No se pudo guardar la imagen.');
    return $nombreFich;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $descripcionParam = ($descripcion === '') ? null : $descripcion;

    try {
        $nuevaImagen = subirImagenCategoria($_FILES['imagen'] ?? null);
        $imagenFinal = $nuevaImagen ?? $imagenActual;

        CategoriaSA::actualizar($id, $nombre, $descripcionParam, $imagenFinal);

        if ($nuevaImagen && $imagenActual) {
            $old = RAIZ_APP . '/img/categorias/' . $imagenActual;
            if (is_file($old)) @unlink($old);
        }

        $app->putAtributoPeticion('msg', 'Categoría actualizada.');
        header('Location: ' . RUTA_APP . '/gerente/categorias_listar.php');
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Editar categoría';

ob_start();
require RAIZ_APP . '/includes/vistas/gerente/categorias_editar_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';