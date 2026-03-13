<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

// Seguridad: Solo Gerentes
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
        <div class="alerta-error"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: #f4f4f4; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 15px;">Nº Pedido</th>
                    <th style="padding: 15px;">Fecha / Hora</th>
                    <th style="padding: 15px;">Cliente ID</th>
                    <th style="padding: 15px;">Tipo</th>
                    <th style="padding: 15px;">Estado</th>
                    <th style="padding: 15px;">Total</th>
                    <th style="padding: 15px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): 
                    $colorEstado = match($p->getEstado()) {
                        'recibido' => '#ff9800',
                        'en preparación', 'cocinando' => '#2196f3',
                        'listo cocina' => '#4caf50',
                        'terminado' => '#1b5e20',
                        'entregado' => '#757575',
                        default => '#333'
                    };
                ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px; font-weight: bold;">#<?= $p->getNumeroPedido() ?></td>
                        <td style="padding: 15px; font-size: 0.9em;"><?= date('d/m/Y H:i', strtotime($p->getFechaHora())) ?></td>
                        <td style="padding: 15px;">User ID: <?= $p->getIdCliente() ?? 'Invitado' ?></td>
                        <td style="padding: 15px;"><span class="tag"><?= ucfirst($p->getTipo()) ?></span></td>
                        <td style="padding: 15px;">
                            <span style="color: <?= $colorEstado ?>; font-weight: bold; font-size: 0.85em;">
                                ● <?= strtoupper($p->getEstado()) ?>
                            </span>
                        </td>
                        <td style="padding: 15px; font-weight: bold;"><?= number_format($p->getTotal(), 2) ?>€</td>
                        <td style="padding: 15px;">
                            <a href="../cliente/pedido_detalle.php?id=<?= $p->getId() ?>" class="btn-sm">Detalles</a>
                            </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
    .tag { background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 0.8em; }
    .btn-sm { 
        background: #333; color: #fff; padding: 5px 10px; 
        text-decoration: none; border-radius: 4px; font-size: 0.8em;
    }
    .btn-sm:hover { background: #d32f2f; }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';