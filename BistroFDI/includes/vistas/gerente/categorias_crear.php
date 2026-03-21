<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/vistas/formularios/FormularioCategoria.php';

$form = new FormularioCategoria();

$tituloPagina = 'Nueva categoría';

ob_start();
?>
<section class="ger-wrap">
    <div class="card">
        <?= $form->gestiona() ?? '' ?>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';