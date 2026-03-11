<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

// Verificación de acceso: solo usuarios logueados
if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = "Mis Pedidos";

// Recuperamos el ID de la sesión (usando tu clave corregida)
$idCliente = (int)($_SESSION['usuario_id'] ?? 0);

// Obtenemos los pedidos reales de la base de datos
try {
    $listaPedidos = PedidoSA::listarPorCliente($idCliente);
} catch (Exception $e) {
    $errorCarga = $e->getMessage();
    $listaPedidos = [];
}

ob_start();
?>

<section class="ger-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h1>Mis Pedidos</h1>
        <a href="<?= RUTA_VISTAS ?>/tienda/categorias_listar.php" class="btn btn-light" style="font-size: 0.9em;">+ Nuevo Pedido</a>
    </div>

    <?php if (isset($errorCarga)): ?>
        <div class="card" style="border-left: 5px solid #d32f2f; color: #d32f2f;">
            Error al cargar tus pedidos: <?= h($errorCarga) ?>
        </div>
    <?php elseif (empty($listaPedidos)): ?>
        <div class="card" style="text-align: center; padding: 50px;">
            <p class="muted" style="font-size: 1.2em;">Aún no has realizado ningún pedido.</p>
            <p>¡Explora nuestra carta y haz tu primer pedido hoy mismo!</p>
            <br>
            <a href="<?= RUTA_VISTAS ?>/tienda/categorias_listar.php" class="btn">Ver la Carta</a>
        </div>
    <?php else: ?>
        <div class="stack" style="gap: 15px;">
            <?php foreach ($listaPedidos as $pedido): 
                // Mapeo de colores según el ENUM de tu base de datos
                $estado = $pedido->getEstado();
                $colorEstado = match($estado) {
                    'nuevo', 'recibido' => '#ff9800',       // Naranja
                    'en preparación', 'cocinando' => '#2196f3', // Azul
                    'listo cocina' => '#4caf50',            // Verde
                    'terminado', 'entregado' => '#757575',  // Gris
                    default => '#333'
                };
            ?>
                <div class="card" style="display: flex; justify-content: space-between; align-items: center; border-left: 6px solid <?= $colorEstado ?>; padding: 15px 25px;">
                    
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 style="margin: 0;">Pedido #<?= $pedido->getNumeroPedido() ?></h3>
                            <span style="background: <?= $colorEstado ?>; color: white; padding: 2px 10px; border-radius: 20px; font-size: 0.75em; font-weight: bold; text-transform: uppercase;">
                                <?= h($estado) ?>
                            </span>
                        </div>
                        <p class="muted" style="margin: 8px 0 0 0; font-size: 0.9em;">
                            📅 <?= date('d/m/Y H:i', strtotime($pedido->getFechaHora())) ?> 
                            <span style="margin: 0 10px;">|</span> 
                            📍 <?= ucfirst($pedido->getTipo()) ?>
                        </p>
                    </div>

                    <div style="text-align: right; min-width: 120px;">
                        <div style="font-size: 1.3em; font-weight: bold; color: #333; margin-bottom: 5px;">
                            <?= number_format($pedido->getTotal(), 2) ?>€
                        </div>
                        <a href="pedido_detalle.php?id=<?= $pedido->getId() ?>" class="btn-link" style="font-size: 0.85em; text-decoration: none; color: #d32f2f; font-weight: bold;">
                            VER DETALLES →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<style>
    .btn-link:hover { text-decoration: underline !important; }
    .card { transition: transform 0.2s; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/plantilla.php';