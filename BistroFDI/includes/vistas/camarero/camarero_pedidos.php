<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

$esGerente = !empty($_SESSION['esGerente']);
$esCamarero = !empty($_SESSION['esCamarero']);

$nombreCamarero = $_SESSION['nombre'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'avatares/default.jpg';
$avatarCamareroUrl = RUTA_IMGS . '/' . ltrim((string)$avatarCamarero, '/');

if (!isset($_SESSION['login']) || (!$esGerente && !$esCamarero)) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = "Estado de los pedidos (Camarero)";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
    $accion = $_POST['accion'] ?? '';
    
    if ($idPedido) {
        try {
            switch($accion) {
                case 'cobrar':
                    PedidoSA::registrarPago($idPedido);
                    break;
                case 'preparar_entrega':
                    PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_TERMINADO);
                    break;
                case 'entregar':
                    PedidoSA::cambiarEstado($idPedido, PedidoSA::ESTADO_ENTREGADO);
                    break;
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    $todosLosPedidos = PedidoSA::listarTodos(); 
    $pedidosCamarero = [];

    foreach ($todosLosPedidos as $p) {
        $estado = $p->getEstado();
        if (in_array($estado, ['recibido', 'listo cocina', 'terminado'])) {
            $pedidosCamarero[] = $p;
        }
    }
} catch (Exception $e) {
    $error = "Error al cargar pedidos: " . $e->getMessage();
    $pedidosCamarero = [];
}

ob_start();
?>

<div class="camarero-topbar">
    <span class="camarero-brand">BISTRO FDI</span>
    <div class="camarero-user">
        <span><?= htmlspecialchars($nombreCamarero) ?></span>
        
        <img src="<?= h($avatarCamareroUrl) ?>" 
             alt="Avatar"
             class="camarero-avatar"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        
        <div class="camarero-avatar-fallback">
            <?= strtoupper(substr($nombreCamarero, 0, 1)) ?>
        </div>
    </div>
</div>

<h2 class="page-title-pad">ESTADO DE LOS PEDIDOS (CAMARERO)</h2>

<?php if (isset($error)): ?>
    <div class="alert-error-soft">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="orders-grid">
    
    <?php foreach ($pedidosCamarero as $p): 
        $estado = $p->getEstado();
        
        $cardClass = match($estado) {
            'recibido' => 'order-card order-card--recibido',
            'listo cocina' => 'order-card order-card--listo',
            'terminado' => 'order-card order-card--terminado',
            default => 'order-card'
        };
        
        $accion = match($estado) {
            'recibido' => 'cobrar',
            'listo cocina' => 'preparar_entrega',
            'terminado' => 'entregar'
        };
        
        $textoBoton = match($estado) {
            'recibido' => '💰 COBRAR',
            'listo cocina' => '📦 PREPARAR ENTREGA',
            'terminado' => '✅ ENTREGAR'
        };
        
        $btnClass = match($estado) {
            'recibido' => 'btn-order btn-order--recibido',
            'listo cocina' => 'btn-order btn-order--listo',
            'terminado' => 'btn-order btn-order--terminado'
        };
    ?>
    
        <div class="<?= $cardClass ?>">
            <div class="order-card-head">
                <div class="order-card-head-top">
                    <h3 class="title-reset order-info-strong">Pedido #<?= $p->getNumeroPedido() ?></h3>
                    <span class="order-type-badge">
                        <?= $p->getTipo() === 'local' ? 'LOCAL' : 'LLEVAR' ?>
                    </span>
                </div>
                <p class="order-time"><?= date('H:i', strtotime($p->getFechaHora())) ?></p>
            </div>
            
            <div class="order-card-body">
                <p class="order-info">Cliente ID: <?= $p->getIdCliente() ?></p>
                <p class="order-info-strong"><strong><?= number_format($p->getTotal(), 2) ?>€</strong></p>
                <p class="order-info">Estado: <strong><?= htmlspecialchars($estado) ?></strong></p>

                <?php if ($estado === 'listo cocina'): ?>
                    <div class="order-review-wrap">
                        <a href="<?= RUTA_VISTAS ?>/cliente/pedido_detalle.php?id=<?= $p->getId() ?>"
                           class="order-review-link">
                            🔍 REVISAR PRODUCTOS
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="order-actions">
                    <form method="POST" action="" class="inline-form">
                        <input type="hidden" name="id_pedido" value="<?= $p->getId() ?>">
                        <input type="hidden" name="accion" value="<?= $accion ?>">
                        <button type="submit" class="<?= $btnClass ?>">
                            <?= $textoBoton ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    <?php endforeach; ?>
    
    <?php if (empty($pedidosCamarero)): ?>
        <div class="order-empty">
            <p class="text-large text-muted-3">No hay pedidos que gestionar.</p>
        </div>
    <?php endif; ?>
    
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';