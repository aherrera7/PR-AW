<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
// Eliminamos requireGerente() para que clientes y visitantes puedan entrar

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app  = Aplicacion::getInstance();

// Detectamos el rol del usuario actual
$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

// Definimos las rutas base para no liarnos con los enlaces
$baseGerente = RUTA_APP . '/includes/vistas/gerente';
$baseUsuario = RUTA_APP . '/includes/vistas/usuarios';

$mensaje    = $app->getAtributoPeticion('msg');
$categorias = CategoriaSA::listar();

$tituloPagina = $esGerente ? 'Gestión de Categorías' : 'Nuestra Carta';

ob_start();
?>
<section class="ger-wrap">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px;">
    <h1><?= h($tituloPagina) ?></h1>
    
    <?php if ($esGerente): ?>
      <a class="btn" href="<?= h($baseGerente.'/categorias_crear.php') ?>">+ Nueva categoría</a>
    <?php endif; ?>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="ger-flash"><?= h((string)$mensaje) ?></div>
  <?php endif; ?>

  <?php if (empty($categorias)): ?>
    <p class="muted">No hay categorías todavía.</p>
  <?php else: ?>
    <div class="stack">
      <?php foreach ($categorias as $c): ?>
        <?php $id = (int)$c->getId(); $img = $c->getImagen(); ?>
        
        <div class="card" style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;">
          <?php if ($img): ?>
            <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$img, '/')) ?>" alt=""
            style="width:220px;max-width:100%;aspect-ratio:4/3;object-fit:cover;border:1px solid #111;border-radius:10px;background:#fff;">
          <?php endif; ?>

          <div class="stack" style="flex:1; min-width:240px;">
            <h3 style="margin:0;"><?= h((string)$c->getNombre()) ?></h3>
            <p class="muted"><?= h((string)($c->getDescripcion() ?? '')) ?></p>

            <div class="form-actions">
              <a class="btn" href="<?= h($baseGerente.'/productos_carta.php?id_cat='.$id) ?>">Acceder</a>

              <?php if ($esGerente): ?>
                <a class="btn btn-light" href="<?= h($baseGerente.'/categorias_editar.php?id='.$id) ?>">Editar</a>
                
                <form action="<?= h($baseGerente.'/categorias_borrar.php') ?>" method="post" style="display:inline;" 
                      onsubmit="return confirm('¿Seguro que quieres borrar esta categoría?');">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <button type="submit" class="btn btn-light" style="color: #d32f2f; border-color: #d32f2f;">Borrar</button>
                </form>
              <?php endif; ?>
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