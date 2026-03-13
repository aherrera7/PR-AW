<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

// 1. Obtener la categoría de la URL
$idCat = (int)($_GET['id_cat'] ?? 0);
$categoria = $idCat > 0 ? CategoriaSA::obtener($idCat) : null;

if (!$categoria) {
    header('Location: categorias_listar.php');
    exit;
}

// 2. Detectar rol
$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
$estaLogueado = !empty($_SESSION['login']);

// 3. Listar productos de esa categoría
// Si es gerente, mostramos todos. Si es cliente, solo los ofertados.
$productos = ProductoSA::listar($idCat, !$esGerente);

$tituloPagina = 'Productos: ' . $categoria->getNombre();
ob_start();
?>

<section class="ger-wrap">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0;"><?= h($categoria->getNombre()) ?></h1>
            <p class="muted"><?= h($categoria->getDescripcion() ?? '') ?></p>
        </div>
        <div style="display:flex; gap:10px;">
            <?php if ($esGerente): ?>
                <a class="btn" href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_crear.php?id_cat=<?= $idCat ?>">+ Nuevo Producto</a>
            <?php endif; ?>
            <a class="btn btn-light" href="categorias_listar.php">Volver</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
        <?php foreach ($productos as $p): ?>
            <?php 
                $id = $p->getId();
                $imagenes = $p->getImagenes();
                if (empty($imagenes)) $imagenes = ['productos/default_producto.jpg'];
                $claseEstado = (!$p->isOfertado()) ? 'style="opacity: 0.6;"' : '';
            ?>
            <div class="card stack" <?= $claseEstado ?> style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                
                <div style="position: relative; width: 100%; aspect-ratio: 1/1; background: #f0f0f0; border-bottom: 1px solid #ddd;">
                    <?php foreach ($imagenes as $index => $ruta): ?>
                        <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$ruta, '/')) ?>" 
                             class="img-carrusel-<?= $id ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; display: <?= $index === 0 ? 'block' : 'none' ?>;">
                    <?php endforeach; ?>

                    <?php if (count($imagenes) > 1): ?>
                        <button onclick="navImg(<?= $id ?>, -1)" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.4); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer;">&#10094;</button>
                        <button onclick="navImg(<?= $id ?>, 1)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.4); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer;">&#10095;</button>
                    <?php endif; ?>
                </div>

                <div class="stack" style="padding: 16px; flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <h3 style="margin: 0;"><?= h($p->getNombre()) ?></h3>
                        <span style="color: #d32f2f; font-weight: bold;"><?= number_format($p->getPrecioFinal(), 2) ?>€</span>
                    </div>
                    
                    <p class="muted" style="font-size: 0.9em; margin: 10px 0; min-height: 3em;">
                        <?= h($p->getDescripcion() ?? '') ?>
                    </p>

                    <div style="margin-top: auto;">
                        <?php if ($esGerente): ?>
                            <div class="form-actions" style="display: flex; gap: 8px;">
                                <a class="btn btn-light" href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_editar.php?id=<?= $id ?>" style="flex:1; text-align:center;">Editar</a>
                                <a class="btn btn-light" 
                                    href="<?= RUTA_APP ?>/includes/vistas/gerente/productos_retirar.php?id=<?= $id ?>&id_cat=<?= $idCat ?>" 
                                    style="color: #d32f2f; border-color: #d32f2f; flex:1; text-align:center;"
                                    onclick="return confirm('¿Cambiar estado de este producto?')">
                                    <?= $p->isOfertado() ? 'Retirar' : 'Reofertar' ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; align-items: center; justify-content: center; border: 1px solid #ccc; border-radius: 8px; width: fit-content; margin: 0 auto;">
                                    <button class="btn-light" onclick="modCant(<?= $id ?>, -1)" style="border:none; padding: 5px 12px;">-</button>
                                    <input type="text" id="cant-<?= $id ?>" value="1" readonly style="width: 40px; text-align: center; border: none; font-weight: bold; background: transparent; color: #333; margin: 0;">
                                    <button class="btn-light" onclick="modCant(<?= $id ?>, 1)" style="border:none; padding: 5px 12px;">+</button>
                                </div>
                                <button class="btn" onclick="addCarrito(<?= $id ?>)">Añadir al carrito</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
function navImg(id, d) {
    const imgs = document.querySelectorAll('.img-carrusel-' + id);
    let cur = 0;
    imgs.forEach((img, i) => { if (img.style.display === 'block') cur = i; });
    imgs[cur].style.display = 'none';
    imgs[(cur + d + imgs.length) % imgs.length].style.display = 'block';
}

function modCant(id, d) {
    const i = document.getElementById('cant-' + id);
    let v = parseInt(i.value) + d;
    if (v >= 1) i.value = v;
}

function addCarrito(id) {
    const logueado = <?= json_encode($estaLogueado) ?>;
    if (!logueado) {
        alert("Debes iniciar sesión para añadir productos al carrito.");
        window.location.href = '<?= RUTA_VISTAS ?>/login.php';
        return;
    }
    const c = document.getElementById('cant-' + id).value;
    window.location.href = 'carrito_gestion.php?action=add&id=' + id + '&cant=' + c;
}
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';