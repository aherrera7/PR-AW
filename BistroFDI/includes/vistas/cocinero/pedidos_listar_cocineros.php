<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

//1. Verificación de acceso (Cocinero o Gerente)
requireGerenteOCocinero();

$tituloPagina = "Panel de Pedidos - Cocina";

//2. Carga de datos reales
try {
    $todosLosPedidos = PedidoSA::listarTodos(); 
    $pedidosCocina = [];

    foreach ($todosLosPedidos as $p) {
        $estado = $p->getEstado();
        if (in_array($estado, ['en preparación', 'cocinando', 'listo cocina']) && PedidoSA::tieneProductosCocina($p->getId())) {
            $pedidosCocina[] = $p;
        }
    }
} catch (Exception $e) {
    $error = "Error al cargar pedidos: " . $e->getMessage();
    $pedidosCocina = [];
}

ob_start();
?>

<section class="ger-wrap">
    <h1>Pedidos en Cocina</h1>
    <p class="muted">Gestión de comandas en tiempo real.</p>

    <?php if (isset($error)): ?>
        <div class="alert-error-soft"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="orders-grid kitchen-grid">
        <?php foreach ($pedidosCocina as $p): 
            $estado = $p->getEstado();

            $cardClass = match($estado) {
                'recibido' => 'order-card order-card--recibido',
                'en preparación' => 'order-card order-card--preparacion',
                'cocinando' => 'order-card order-card--cocinando',
                'listo cocina' => 'order-card order-card--listo',
                default => 'order-card'
            };
        ?>
            <div class="<?= $cardClass ?>">
                <div class="order-card-head kitchen-card-head">
                    <div>
                        <h3 class="title-reset">Pedido #<?= $p->getNumeroPedido() ?></h3>
                        <span class="kitchen-order-type">
                            <?= ($p->getTipo() === 'local') ? '🏠 LOCAL' : '🥡 LLEVAR' ?>
                        </span>
                    </div>
                    <p class="muted kitchen-order-time">
                        <strong><?= date('H:i', strtotime($p->getFechaHora())) ?></strong>
                    </p>
                </div>

                <div class="order-card-body">
                    <p class="kitchen-order-status">
                        Estado:
                        <span class="kitchen-order-status-value">
                            <?= htmlspecialchars($estado) ?>
                        </span>
                    </p>
                    
                    <div class="catalog-actions mt-15">
                        <a href="productos_pedido.php?id_pedido=<?= $p->getId() ?>" class="btn w-100 text-center">
                            <?= ($estado === 'recibido') ? 'EMPEZAR' : 'GESTIONAR' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($pedidosCocina)): ?>
        <div class="card kitchen-empty-state">
            <p class="text-large text-muted-3">No hay pedidos pendientes en cocina.</p>
            <p class="muted">¡Buen trabajo! Descansa un poco.</p>
        </div>
    <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';