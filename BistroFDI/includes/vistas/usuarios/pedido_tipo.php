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

        header('Location: ' . RUTA_VISTAS . '/carrito/categorias_listar.php');
        exit;
    }
}

$tituloPagina = 'Tipo de pedido';

ob_start();
?>

<section class="ger-wrap">
    <div class="card stack" style="max-width: 700px; margin: 40px auto; padding: 30px;">
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
            <label class="card" style="cursor:pointer; padding:16px;">
                <input type="radio" name="tipo" value="local" style="margin-right:10px;">
                <strong>Local</strong>
                <div class="muted">Para consumir en Bistro FDI.</div>
            </label>

            <label class="card" style="cursor:pointer; padding:16px;">
                <input type="radio" name="tipo" value="llevar" style="margin-right:10px;">
                <strong>Llevar</strong>
                <div class="muted">Para recoger y consumir fuera.</div>
            </label>

            <div class="form-actions" style="margin-top: 10px;">
                <button class="btn" type="submit">Continuar</button>
            </div>
        </form>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';