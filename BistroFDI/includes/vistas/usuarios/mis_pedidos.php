<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

requireLogin();

$idCliente = (int)($_SESSION['usuario_id'] ?? 0);

$pedidos = PedidoSA::listarPorCliente($idCliente);

$tituloPagina = 'Mis pedidos';

ob_start();
?>

<section class="ger-wrap">

<h1>Mis pedidos</h1>

<?php if (empty($pedidos)): ?>

<div class="card stack text-center p-30">
<p class="muted">No tienes pedidos activos en este momento.</p>

<a class="btn" href="<?= RUTA_APP ?>/includes/vistas/usuarios/categorias_listar.php">
Ver la carta
</a>
</div>

<?php else: ?>

<div class="stack">

<?php foreach ($pedidos as $p): ?>

<?php
$estado = $p->getEstado();
$estadoClass = 'user-order-status-default';

if ($estado === 'en preparación') $estadoClass = 'user-order-status-preparacion';
if ($estado === 'cocinando') $estadoClass = 'user-order-status-cocinando';
if ($estado === 'listo cocina') $estadoClass = 'user-order-status-listo';
if ($estado === 'terminado') $estadoClass = 'user-order-status-terminado';
if ($estado === 'entregado') $estadoClass = 'user-order-status-entregado';
?>

<div class="card p-20">

<div class="user-order-head">

<div>
<strong>Pedido #<?= $p->getNumeroPedido() ?></strong><br>
<span class="muted">
<?= date('d/m/Y H:i', strtotime($p->getFechaHora())) ?>
</span>
</div>

<div>
Estado:<br>
<strong class="<?= $estadoClass ?>">
<?= ucfirst($estado) ?>
</strong>
</div>

<div>
Total:<br>
<strong><?= number_format($p->getTotal(),2) ?> €</strong>
</div>

</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';