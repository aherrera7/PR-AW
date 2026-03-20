<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/vistas/formularios/FormularioEditarPerfil.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
    header('Location: ' . RUTA_APP . '/includes/vistas/login.php');
    exit;
}

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

$idObjetivo = (int)($_SESSION['usuario_id']);
if ($esGerente && isset($_GET['id'])) {
    $idObjetivo = (int)$_GET['id'];
}

$form = new FormularioEditarPerfil($idObjetivo, $esGerente);

$htmlFormulario = $form->gestiona();

$tituloPagina = $form->getTituloPagina();

ob_start();
?>
<section id="contenido">
    <h2><?= htmlspecialchars($tituloPagina, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
    <div class="card">
        <?= $htmlFormulario ?? '' ?>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';