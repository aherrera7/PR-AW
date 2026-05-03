<?php
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente(); // Solo gerentes
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';

$ofertas = OfertaSA::listarTodas();
$tituloPagina = 'Panel de Gestión de Ofertas';

ob_start();
?>
<section class="ger-wrap">
    <div class="header-bar">
        <h1>⚙️ Gestión de Ofertas</h1>
        <a href="ofertas_gestion.php" class="btn">Nueva Oferta</a>
    </div>

    <div class="card">
        <table class="ofertas-admin-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Vigencia</th>
                    <th>Dto.</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ofertas as $o): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($o->getNombre()) ?></strong></td>
                    <td><?= $o->getFechaInicio() ?> / <?= $o->getFechaFin() ?></td>
                    <td><?= $o->getDescuento() * 100 ?>%</td>
                    <td>
                        <span class="estado-tag <?= $o->isActiva() ? 'activa' : 'inactiva' ?>">
                            <?= $o->isActiva() ? 'Activa' : 'Pausada' ?>
                        </span>
                    </td>
                    <td>
                        <a href="ofertas_gestion.php?id=<?= $o->getId() ?>">📝 Editar</a>
                        </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';

/*
Para eliminar oferta:
En el foreach:
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $o->getId() ?>">
                            <button type="submit" name="eliminar" onclick="return confirm('¿Seguro que quieres eliminar esta oferta?')">
                                🗑️ Eliminar
                            </button>
                        </form>

Y al principio del archivo, antes de listarTodas():
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $id = $_POST['id'] ?? null;
    if ($id) {OfertaSA::delete((int)$id);}
    header("Location: ofertas_admin.php");
    exit();
}
*/