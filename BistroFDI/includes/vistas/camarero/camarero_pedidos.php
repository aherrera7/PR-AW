<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

//1. Verificación de acceso (Camarero o Gerente)
requireGerenteOCamarero();

$nombreCamarero = $_SESSION['nombre'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'avatares/default.jpg';
$avatarCamareroUrl = RUTA_IMGS . '/' . ltrim((string)$avatarCamarero, '/');

$tituloPagina = "Estado de los pedidos (Camarero)";

//2. Procesamiento de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
    $accion = $_POST['accion'] ?? '';
    if ($idPedido) {
        try {
            switch ($accion) {
                case 'cobrar':
                    PedidoSA::registrarPago($idPedido);
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

//3. Carga de datos
try {
    $todosLosPedidos = PedidoSA::listarTodos();
    $pedidosCamarero = [];
    
    foreach ($todosLosPedidos as $p) {
        $estado = $p->getEstado();
        // Mostrar pedidos en recibido, listo cocina, terminado
        if (in_array($estado, ['recibido', 'listo cocina', 'terminado'])) {
            $soloBebidas = PedidoSA::soloBebidas($p->getId());
            
            $pedidosCamarero[] = [
                'id' => $p->getId(),
                'numeroPedido' => $p->getNumeroPedido(),
                'tipoTexto' => $p->getTipo() === 'local' ? '🏠 LOCAL' : '🥡 LLEVAR',
                'hora' => date('H:i', strtotime($p->getFechaHora())),
                'idCliente' => $p->getIdCliente(),
                'totalFormateado' => number_format($p->getTotal(), 2) . ' €',
                'estado' => $estado,
                'soloBebidas' => $soloBebidas,
                'cardClass' => $soloBebidas ? 'order-card order-card--solo-bebidas' : 'order-card'
            ];
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
    <?php foreach ($pedidosCamarero as $p): ?>
        <div class="<?= $p['cardClass'] ?>">
            <div class="order-card-head">
                <div class="order-card-head-top">
                    <h3 class="title-reset order-info-strong">
                        Pedido #<?= h((string) $p['numeroPedido']) ?>
                        <?php if ($p['soloBebidas']): ?>
                            <span class="solo-bebidas-badge">🥤 Solo bebidas</span>
                        <?php endif; ?>
                    </h3>
                    <span class="order-type-badge">
                        <?= h($p['tipoTexto']) ?>
                    </span>
                </div>
                <p class="order-time"><?= h($p['hora']) ?></p>
            </div>

            <div class="order-card-body">
                <p class="order-info">Cliente ID: <?= h((string) $p['idCliente']) ?></p>
                <p class="order-info-strong">
                    <strong><?= h($p['totalFormateado']) ?></strong>
                </p>
                <p class="order-info">
                    Estado: <strong><?= h($p['estado']) ?></strong>
                </p>

                <div class="order-actions">
                    <?php if ($p['estado'] === 'recibido'): ?>
                        <a href="productos_pedido_camarero.php?id_pedido=<?= h((string) $p['id']) ?>" class="btn w-100 text-center">
                            💰 COBRAR
                        </a>
                    <?php elseif ($p['estado'] === 'listo cocina'): ?>
                        <a href="productos_pedido_camarero.php?id_pedido=<?= h((string) $p['id']) ?>" class="btn w-100 text-center">
                            🥤 PREPARAR BEBIDAS
                        </a>
                    <?php elseif ($p['estado'] === 'terminado'): ?>
                        <form method="POST" action="" class="inline-form" style="width: 100%;">
                            <input type="hidden" name="id_pedido" value="<?= h((string) $p['id']) ?>">
                            <input type="hidden" name="accion" value="entregar">
                            <button type="submit" class="btn w-100 text-center">
                                📦 ENTREGAR
                            </button>
                        </form>
                    <?php endif; ?>
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