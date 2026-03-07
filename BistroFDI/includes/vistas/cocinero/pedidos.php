<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';

requireLogin();
if (!isCocinero()) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

$app = Aplicacion::getInstance();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coger_id'])) {
    $idPedido = (int)($_POST['coger_id'] ?? 0);

    try {
        $ok = PedidoSA::cogerPedido($idPedido);

        if ($ok) {
            $app->putAtributoPeticion('msg', 'Pedido pasado a cocinando.');
            header('Location: ' . RUTA_VISTAS . '/cocinero/pedidos.php');
            exit;
        } else {
            $errores[] = 'No se pudo coger el pedido. Puede que ya no esté en preparación.';
        }
    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

$pendientes = PedidoSA::listarEnPreparacion();
$cocinando = PedidoSA::listarCocinando();
$flash = $app->getAtributoPeticion('msg');

$tituloPagina = 'Pedidos de cocina';

ob_start();
?>

<section class="ger-wrap">
  <div class="ger-head">
    <h1>Pedidos de cocina</h1>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="ger-flash">
      <?= h((string)$flash) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errores)): ?>
    <div class="ger-flash ger-flash--err">
      <strong>No se pudo completar la acción:</strong>
      <ul>
        <?php foreach ($errores as $e): ?>
          <li><?= h((string)$e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card stack" style="margin-bottom: 20px;">
    <h2>Pedidos en preparación</h2>

    <?php if (empty($pendientes)): ?>
      <p class="muted">No hay pedidos en preparación.</p>
    <?php else: ?>
      <ul class="userlist">
        <?php foreach ($pendientes as $p): ?>
          <li class="row">
            <div>
              <strong>Pedido #<?= h((string)$p->getNumeroPedido()) ?></strong>
              <div class="muted">
                Tipo: <?= h((string)$p->getTipo()) ?> ·
                Estado: <?= h((string)$p->getEstado()) ?> ·
                Total: <?= h(number_format($p->getTotal(), 2)) ?> €
              </div>
              <div class="muted">
                Fecha: <?= h((string)$p->getFechaHora()) ?>
              </div>
            </div>

            <div class="actions">
              <form method="post">
                <input type="hidden" name="coger_id" value="<?= h((string)$p->getId()) ?>">
                <button class="btn" type="submit">Quedarme con este pedido</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="card stack">
    <h2>Pedidos cocinando</h2>

    <?php if (empty($cocinando)): ?>
      <p class="muted">No hay pedidos cocinándose ahora mismo.</p>
    <?php else: ?>
      <ul class="userlist">
        <?php foreach ($cocinando as $p): ?>
          <li class="row">
            <div>
              <strong>Pedido #<?= h((string)$p->getNumeroPedido()) ?></strong>
              <div class="muted">
                Tipo: <?= h((string)$p->getTipo()) ?> ·
                Estado: <?= h((string)$p->getEstado()) ?> ·
                Total: <?= h(number_format($p->getTotal(), 2)) ?> €
              </div>
              <div class="muted">
                Fecha: <?= h((string)$p->getFechaHora()) ?>
              </div>
            </div>

            <div class="actions">
              <a class="btn" href="<?= h(RUTA_VISTAS . '/cocinero/pedido_detalle.php?id=' . (int)$p->getId()) ?>">
                Ver detalle
              </a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';