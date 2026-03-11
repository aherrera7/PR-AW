<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';

requireLogin();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = (string)($_POST['tipo'] ?? '');

    if ($tipo !== 'local' && $tipo !== 'llevar') {
        $errores[] = 'Debes seleccionar un tipo de pedido válido.';
    } else {
        $_SESSION['pedido_tipo'] = $tipo;

        // Redirigimos a las categorias de carta
        header('Location: ' . RUTA_VISTAS . '/usuarios/categorias_listar.php');
        exit;
    }
}

$tituloPagina = 'Tipo de pedido';

ob_start();
?>

<section class="ger-wrap">
    <div class="card stack choice-card">
        <h1>¿Cómo quieres tu pedido?</h1>
        <p class="muted">Selecciona si vas a consumir en Bistro FDI o si prefieres recogerlo para llevar.</p>

        <?php if (!empty($errores)): ?>
            <div class="ger-flash ger-flash--err">
                <strong>Revisa esto:</strong>
                <ul>
                    <?php foreach ($errores as $e): ?>
                        <li><?= h((string)$e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="stack">
            <label class="card choice-option">
                <input type="radio" name="tipo" value="local">
                <strong>Local</strong>
                <div class="muted">Para consumir en Bistro FDI.</div>
            </label>

            <label class="card choice-option">
                <input type="radio" name="tipo" value="llevar">
                <strong>Llevar</strong>
                <div class="muted">Para recoger y consumir fuera.</div>
            </label>

            <div class="form-actions">
                <button class="btn" type="submit">Continuar</button>
            </div>
        </form>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';