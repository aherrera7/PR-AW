<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';


$app  = Aplicacion::getInstance();

$baseUsuario = RUTA_APP . '/includes/vistas/usuarios';

$mensaje    = $app->getAtributoPeticion('msg');
$categorias = CategoriaSA::listar();
$ofertas = OfertaSA::listarActivasHoy();

$tituloPagina = 'Nuestra Carta';

ob_start();
?>
<section class="ger-wrap">
  <div class="header-bar">
    <h1><?= h($tituloPagina) ?></h1>

    <a class="btn" href="<?= h(RUTA_APP . '/includes/vistas/usuarios/productos_carta.php') ?>">
      Ver todos los productos
    </a>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="ger-flash"><?= h((string)$mensaje) ?></div>
  <?php endif; ?>

  <?php if (!empty($ofertas)): ?>
  <div class="card" style="margin-bottom: 20px; background: #fff8e8; border: 1px solid #f0d98a; padding: 20px;">
    <div class="header-bar" style="align-items: center; gap: 15px;">
      <div>
        <h2 style="margin: 0 0 6px 0;">🎁 Ofertas disponibles</h2>
        <p class="muted" style="margin: 0;">
          Descubre nuestros packs con descuento y consulta cuánto te ahorras.
        </p>
      </div>

      <a class="btn" href="<?= h(RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php') ?>">
        Ver ofertas
      </a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($categorias)): ?>
    <p class="muted">No hay categorías todavía.</p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($categorias as $c): ?>
        <?php $id = (int)$c->getId(); $img = $c->getImagen(); ?>
        
        <div class="card cat-card">
          <?php if ($img): ?>
            <img
              src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>"
              alt=""
              class="cat-img">
          <?php endif; ?>

          <div class="stack flex-1 minw-240">
            <h3 class="title-reset"><?= h((string)$c->getNombre()) ?></h3>
            <p class="muted"><?= h((string)($c->getDescripcion() ?? '')) ?></p>

            <div class="form-actions">
              <a class="btn" href="<?= h($baseUsuario . '/productos_carta.php?id_cat=' . $id) ?>">Acceder</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';