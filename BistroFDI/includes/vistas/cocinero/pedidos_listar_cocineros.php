<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

// Control de acceso (Cocinero o Gerente)
$esGerente = !empty($_SESSION['esGerente']);
$esCocinero = !empty($_SESSION['esCocinero']);

if (!isset($_SESSION['login']) || (!$esGerente && !$esCocinero)) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = "Panel de Pedidos - Cocina";

// CARGA DE DATOS REALES
try {
    // Obtenemos todos los pedidos para filtrar los que están en proceso
    $todosLosPedidos = PedidoSA::listarTodos(); 
    $pedidosCocina = [];

    foreach ($todosLosPedidos as $p) {
        $estado = $p->getEstado();
        // Solo mostramos pedidos que no estén terminados ni entregados
        if (in_array($estado, [ 'en preparación', 'cocinando', 'listo cocina'])) {
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
        <div style="color: red; padding: 10px; background: #fee; border-radius: 5px;"><?= $error ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php foreach ($pedidosCocina as $p): 
            $estado = $p->getEstado();
            
            // Color de fondo dinámico según urgencia o estado
            $bgColor = match($estado) {
                'recibido' => '#fff3e0',       // Naranja claro (Nuevo)
                'en preparación' => '#e3f2fd', // Azul claro
                'cocinando' => '#f3e5f5',      // Morado claro
                'listo cocina' => '#e8f5e9',   // Verde claro
                default => '#ffffff'
            };
        ?>
            <div class="card" style="padding: 0; border: 1px solid #ddd; background-color: <?= $bgColor ?>; overflow: hidden;">
                <div style="padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0;">Pedido #<?= $p->getNumeroPedido() ?></h3>
                        <span style="font-size: 0.8em; font-weight: bold; text-transform: uppercase; color: #555;">
                            <?= ($p->getTipo() === 'local') ? '🏠 LOCAL' : '🥡 LLEVAR' ?>
                        </span>
                    </div>
                    <p class="muted" style="margin: 0; font-size: 0.9em;">
                        <strong><?= date('H:i', strtotime($p->getFechaHora())) ?></strong>
                    </p>
                </div>

                <div style="padding: 15px;">
                    <p style="margin: 5px 0;">Estado: 
                        <span style="font-weight: bold; color: #d32f2f; text-transform: uppercase; font-size: 0.85em;">
                            <?= $estado ?>
                        </span>
                    </p>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <a href="productos_pedido.php?id_pedido=<?= $p->getId() ?>" class="btn" style="flex: 1; text-align: center; text-decoration: none; padding: 10px;">
                            <?= ($estado === 'recibido') ? 'EMPEZAR' : 'GESTIONAR' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($pedidosCocina)): ?>
        <div class="card" style="text-align: center; padding: 60px; background: #f9f9f9;">
            <p style="font-size: 1.2em; color: #666;">No hay pedidos pendientes en cocina.</p>
            <p class="muted">¡Buen trabajo! Descansa un poco.</p>
        </div>
    <?php endif; ?>
</section>

<style>
    .card { transition: transform 0.2s, box-shadow 0.2s; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
    .btn { background: #333; color: white; border-radius: 4px; border: none; cursor: pointer; }
    .btn:hover { background: #d32f2f; }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';