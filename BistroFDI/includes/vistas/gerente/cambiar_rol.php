<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/vistas/formularios/FormularioCambiarRol.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . RUTA_VISTAS . '/gerente/usuarios.php');
    exit;
}

$form = new FormularioCambiarRol($id);
$htmlFormulario = $form->gestiona();

$tituloPagina = 'Cambiar rol';

ob_start();
?>

<section class="ger-wrap">
  <div class="header-bar">
    <h1>Cambiar rol</h1>
    <a class="btn btn-light" href="<?= h(RUTA_VISTAS . '/gerente/usuarios.php') ?>">Volver</a>
  </div>

  <div class="card stack">
    <?= $htmlFormulario ?? '' ?>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';