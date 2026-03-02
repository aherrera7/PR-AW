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
$imagen = $categoria->getImagen();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        CategoriaSA::borrar($id);

        if ($imagen) {
            $path = RAIZ_APP . '/img/categorias/' . $imagen;
            if (is_file($path)) @unlink($path);
        }

        $app->putAtributoPeticion('msg', 'Categoría borrada.');
        header('Location: ' . RUTA_APP . '/gerente/categorias_listar.php');
        exit;
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$tituloPagina = 'Borrar categoría';

ob_start();
require RAIZ_APP . '/includes/vistas/gerente/categorias_borrar_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';