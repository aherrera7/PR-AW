<?php
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente(); // Seguridad: solo gerentes

require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';
require_once RAIZ_APP . '/includes/vistas/formularios/formularioOferta.php';

// Si viene un ID por la URL, es que estamos editando
$idOferta = $_GET['id'] ?? null;
$tituloPagina = $idOferta ? 'Editar Oferta' : 'Nueva Oferta';

// Instanciamos el formulario pasándole el ID si existe
$form = new FormularioOferta($idOferta);

ob_start();
?>
<section class="ger-wrap">
    <div class="header-bar">
        <h1><?= $idOferta ? '📝 Editar Oferta' : '🎁 Crear Nueva Oferta' ?></h1>
        <a href="ofertas_admin.php" class="btn">Volver al listado</a>
    </div>

    <div class="card">
        <?= $form->gestiona() ?>
    </div>
</section>

<script src="<?= RUTA_JS ?>/ofertas_gestion.js"></script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';