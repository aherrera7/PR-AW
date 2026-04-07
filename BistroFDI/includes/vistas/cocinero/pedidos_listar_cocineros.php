<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/util/listarCocinero_helper.php';

// Verificación de acceso (Cocinero o Gerente)
requireGerenteOCocinero();

$tituloPagina = "Panel de Pedidos - Cocina";

// Obtener pedidos usando el helper
$datos = ListarCocineroHelper::obtenerPedidosCocina();
$error = $datos['error'];
$pedidosFormateados = ListarCocineroHelper::formatearPedidosParaVista($datos['pedidos']);

ob_start();
?>

<section class="ger-wrap">
    <h1>Pedidos en Cocina</h1>
    <p class="muted">Gestión de comandas en tiempo real.</p>

    <?php if ($error): ?>
        <div class="alert-error-soft"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?= ListarCocineroHelper::generarHtmlPedidos($pedidosFormateados) ?>

</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';