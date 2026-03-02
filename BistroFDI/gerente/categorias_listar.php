<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();
$mensaje = $app->getAtributoPeticion('msg');

$categorias = CategoriaSA::listar();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$tituloPagina = 'Gestión de Categorías';

$cards = '';
foreach ($categorias as $c) {
    $id = (int)$c->getId();
    $img = $c->getImagen();
    $imgHtml = $img
        ? '<img class="bf-card-img" src="'.h(RUTA_IMGS.'/categorias/'.$img).'" alt="">'
        : '<div class="bf-card-img bf-card-img--empty" aria-hidden="true"></div>';

    $cards .= '
      <article class="bf-card">
        '.$imgHtml.'
        <h3 class="bf-card-title">'.h($c->getNombre()).'</h3>
        <p class="bf-card-desc">'.h($c->getDescripcion() ?? '').'</p>
        <div class="bf-card-actions">
          <a class="bf-btn" href="'.h(RUTA_APP.'/gerente/categorias_editar.php?id='.$id).'">Editar</a>
          <a class="bf-btn bf-btn-danger" href="'.h(RUTA_APP.'/gerente/categorias_borrar.php?id='.$id).'">Borrar</a>
        </div>
      </article>
    ';
}

$flashHtml = $mensaje ? '<div class="bf-flash">'.h((string)$mensaje).'</div>' : '';

$contenidoPrincipal = '
<style>
  .bf-wrap{ max-width: 1100px; margin: 0 auto; }
  .bf-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin: 6px 0 18px; }
  .bf-head h1{ font-size: 30px; margin:0; letter-spacing: 1px; }
  .bf-btn{ display:inline-block; border: 1px solid #111; padding: 8px 12px; border-radius: 8px; text-decoration:none; color:#111; background:#fff; font-weight:700; }
  .bf-btn:hover{ text-decoration: underline; }
  .bf-btn-primary{ background:#111; color:#fff; }
  .bf-btn-danger{ background:#fff; color:#b00020; border-color:#b00020; }
  .bf-flash{ border: 1px solid #111; background:#e6e6e6; padding:10px 12px; border-radius: 10px; margin-bottom: 14px; }
  .bf-grid{ display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 26px; }
  .bf-card{ border: 2px solid #111; border-radius: 10px; background:#fff; padding: 14px; display:flex; flex-direction:column; gap: 10px; }
  .bf-card-img{ width:100%; aspect-ratio: 4 / 3; border: 2px solid #111; border-radius: 8px; object-fit: cover; background:#fff; }
  .bf-card-img--empty{ display:block; background: repeating-linear-gradient(45deg, #fff, #fff 10px, #f0f0f0 10px, #f0f0f0 20px); }
  .bf-card-title{ margin: 4px 0 0; font-size: 18px; }
  .bf-card-desc{ margin:0; min-height: 42px; }
  .bf-card-actions{ display:flex; gap:10px; margin-top:auto; }
  .bf-empty{ opacity:.75; }
  @media (max-width: 1100px){ .bf-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 700px){ .bf-grid{ grid-template-columns: 1fr; } .bf-head{ flex-direction:column; align-items:flex-start; } }
</style>

<section class="bf-wrap">
  <div class="bf-head">
    <h1>Categorías</h1>
    <a class="bf-btn bf-btn-primary" href="'.h(RUTA_APP.'/gerente/categorias_crear.php').'">+ Nueva categoría</a>
  </div>

  '.$flashHtml.'

  '.(empty($categorias)
      ? '<p class="bf-empty">No hay categorías todavía.</p>'
      : '<div class="bf-grid">'.$cards.'</div>').'
</section>
';

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';