<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

requireLogin();

// id del usuario logueado
$idCliente = (int)($_SESSION['usuario_id'] ?? 0);

// obtener pedidos del usuario
$pedidos = PedidoSA::obtenerActivosPorCliente($idCliente);

$tituloPagina = "Mis pedidos";

ob_start();
?>

<section class="ger-wrap">

<h1>Mis pedidos</h1>

<?php if (empty($pedidos)): ?>

<div class="card stack" style="text-align:center; padding:30px;">
    <p class="muted">Todavía no has realizado ningún pedido.</p>

    <a class="btn" href="<?= RUTA_APP ?>/includes/vistas/usuarios/categorias_listar.php">
        Ver la carta
    </a>
</div>

<?php else: ?>

<div class="stack">

<?php foreach ($pedidos as $p): ?>

<div class="card" style="padding:20px;">

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">

<div>
<strong>Pedido #<?= $p->getNumeroPedido() ?></strong><br>
<span class="muted">
<?= date('d/m/Y H:i', strtotime($p->getFechaHora())) ?>
</span>
</div>

<div>
Estado:<br>
<strong><?= ucfirst($p->getEstado()) ?></strong>
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