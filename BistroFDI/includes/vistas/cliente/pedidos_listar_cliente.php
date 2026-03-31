<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

// 1. Verificación de acceso: solo usuarios logueados
requireLogin();

$tituloPagina = "Mis Pedidos";

// 2. Recuperamos el ID de la sesión
$idCliente = (int)($_SESSION['usuario_id'] ?? 0);

// 3. Obtenemos los pedidos reales de la base de datos
try {
    $listaPedidos = PedidoSA::listarPorCliente($idCliente);
} catch (Exception $e) {
    $errorCarga = $e->getMessage();
    $listaPedidos = [];
}

ob_start();
?>

<section class="ger-wrap">
    <div class="page-head page-head-compact">
        <h1>Mis Pedidos</h1>
        <a href="<?= RUTA_VISTAS ?>/usuarios/categorias_listar.php" class="btn btn-light">+ Nuevo Pedido</a>
    </div>

    <?php if (isset($errorCarga)): ?>
        <div class="card card-lift pedidos-error-card">
            Error al cargar tus pedidos: <?= h($errorCarga) ?>
        </div>
    <?php elseif (empty($listaPedidos)): ?>
        <div class="card text-center p-50">
            <p class="text-large text-muted-3 mb-15">Aún no has realizado ningún pedido.</p>
            <p>¡Explora nuestra carta y haz tu primer pedido hoy mismo!</p>
            <br>
            <a href="<?= RUTA_VISTAS ?>/usuarios/categorias_listar.php" class="btn">Ver la Carta</a>
        </div>
    <?php else: ?>
        <div class="pedidos-stack">
            <?php foreach ($listaPedidos as $pedido): 
                $estado = $pedido->getEstado();

                $cardClass = match($estado) {
                    'nuevo', 'recibido' => 'pedido-list-card pedido-list-card--recibido',
                    'en preparación', 'cocinando' => 'pedido-list-card pedido-list-card--preparacion',
                    'listo cocina' => 'pedido-list-card pedido-list-card--listo',
                    'terminado', 'entregado' => 'pedido-list-card pedido-list-card--finalizado',
                    default => 'pedido-list-card'
                };

                $pillClass = match($estado) {
                    'nuevo', 'recibido' => 'pedido-status-pill pedido-status-pill--recibido',
                    'en preparación', 'cocinando' => 'pedido-status-pill pedido-status-pill--preparacion',
                    'listo cocina' => 'pedido-status-pill pedido-status-pill--listo',
                    'terminado', 'entregado' => 'pedido-status-pill pedido-status-pill--finalizado',
                    default => 'pedido-status-pill pedido-status-pill--finalizado'
                };
            ?>
                <div class="card card-lift <?= $cardClass ?>">
                    
                    <div class="pedido-list-main">
                        <div class="pedido-list-top">
                            <h3 class="title-reset">Pedido #<?= $pedido->getNumeroPedido() ?></h3>
                            <span class="<?= $pillClass ?>">
                                <?= h($estado) ?>
                            </span>
                        </div>
                        <p class="pedido-list-meta muted-small text-muted-2">
                            📅 <?= date('d/m/Y H:i', strtotime($pedido->getFechaHora())) ?>
                            <span class="pedido-list-sep">|</span>
                            📍 <?= ucfirst($pedido->getTipo()) ?>
                        </p>
                    </div>

                    <div class="pedido-list-side">
                        <div class="text-xl text-dark mb-0">
                            <strong><?= number_format($pedido->getTotal(), 2) ?>€</strong>
                        </div>
                        <a href="pedido_detalle.php?id=<?= $pedido->getId() ?>" class="btn-link">
                            VER DETALLES →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';