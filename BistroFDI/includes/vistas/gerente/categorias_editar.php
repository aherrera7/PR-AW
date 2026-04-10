<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/vistas/formularios/formularioCategoria.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(404);
    exit('Categoría no encontrada.');
}

$form = new FormularioCategoria($id);

$tituloPagina = 'Editar categoría';

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