<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

if (!isset($_SESSION['login']) || empty($_SESSION['esGerente'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = "Gestión Global de Pedidos";

try {
    $pedidos = PedidoSA::listarTodos();
} catch (Exception $e) {
    $error = $e->getMessage();
}

ob_start();
?>

<section class="ger-wrap">
    <h1>Panel de Control de Pedidos</h1>
    <p class="muted">Aquí puedes ver y gestionar todos los pedidos realizados en Bistro FDI.</p>

    <?php if (isset($error)): ?>
        <div class="alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="card table-container">
        <table class="table-pedidos">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Fecha / Hora</th>
                    <th>Cliente ID</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): 
                    $estadoClass = match($p->getEstado()) {
                        'recibido' => 'estado-recibido',
                        'en preparación', 'cocinando' => 'estado-preparacion',
                        'listo cocina' => 'estado-listo',
                        'terminado' => 'estado-terminado',
                        'entregado' => 'estado-entregado',
                        default => 'estado-default'
                    };
                ?>
                    <tr>
                        <td class="pedido-cell-strong">#<?= $p->getNumeroPedido() ?></td>
                        <td class="pedido-cell-small"><?= date('d/m/Y H:i', strtotime($p->getFechaHora())) ?></td>
                        <td>User ID: <?= $p->getIdCliente() ?? 'Invitado' ?></td>
                        <td><span class="tag"><?= ucfirst($p->getTipo()) ?></span></td>
                        <td>
                            <span class="estado-dot <?= $estadoClass ?>">
                                ● <?= strtoupper($p->getEstado()) ?>
                            </span>
                        </td>
                        <td class="pedido-cell-strong"><?= number_format($p->getTotal(), 2) ?>€</td>
                        <td>
                            <a href="../cliente/pedido_detalle.php?id=<?= $p->getId() ?>" class="btn-sm">Detalles</a>
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