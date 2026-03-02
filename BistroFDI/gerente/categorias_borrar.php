<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

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

$errHtml = '';
if ($errores) {
    $lis = '';
    foreach ($errores as $er) $lis .= '<li>'.h($er).'</li>';
    $errHtml = '<div class="bf-flash bf-flash--err"><strong>No se pudo borrar:</strong><ul>'.$lis.'</ul><div class="bf-muted">Si tiene productos asignados, primero reasigna o retira esos productos.</div></div>';
}

$contenidoPrincipal = '
<style>
  .bf-wrap{ max-width: 850px; margin: 0 auto; }
  .bf-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin: 6px 0 18px; }
  .bf-head h1{ font-size: 30px; margin:0; letter-spacing: 1px; }
  .bf-card{ border:2px solid #111; border-radius:10px; background:#fff; padding: 16px; }
  .bf-actions{ display:flex; gap:10px; margin-top: 12px; }
  .bf-btn{ display:inline-block; border: 1px solid #111; padding: 8px 12px; border-radius: 8px; text-decoration:none; color:#111; background:#fff; font-weight:700; cursor:pointer; }
  .bf-btn-danger{ background:#fff; color:#b00020; border-color:#b00020; }
  .bf-flash{ border: 1px solid #111; background:#e6e6e6; padding:10px 12px; border-radius: 10px; margin-bottom: 14px; }
  .bf-flash--err{ background:#fff1f2; border-color:#b00020; }
  .bf-muted{ opacity:.75; font-size: 14px; }
</style>

<section class="bf-wrap">
  <div class="bf-head">
    <h1>Borrar categoría</h1>
    <a class="bf-btn" href="'.h(RUTA_APP.'/gerente/categorias_listar.php').'">Volver</a>
  </div>

  '.$errHtml.'

  <div class="bf-card">
    <p>Vas a borrar la categoría:</p>
    <p><strong>'.h($categoria->getNombre()).'</strong></p>
    <p class="bf-muted">Esta acción no se puede deshacer.</p>

    <form method="post">
      <div class="bf-actions">
        <button class="bf-btn bf-btn-danger" type="submit" onclick="return confirm(\'¿Seguro que quieres borrar esta categoría?\');">Sí, borrar</button>
        <a class="bf-btn" href="'.h(RUTA_APP.'/gerente/categorias_listar.php').'">Cancelar</a>
      </div>
    </form>
  </div>
</section>
';

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';