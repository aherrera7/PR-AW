<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

$esGerente = !empty($_SESSION['esGerente']);
$esCamarero = !empty($_SESSION['esCamarero']);

$nombreCamarero = $_SESSION['nombre'] ?? 'Camarero';
$avatarCamarero = $_SESSION['avatar'] ?? 'default.jpg';


if (!isset($_SESSION['login']) || (!$esGerente && !$esCamarero)) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = "Estado de los pedidos (Camarero)";


try {
    // Obtenemos todos los pedidos para filtrar los que necesita el camarero
    $todosLosPedidos = PedidoSA::listarTodos(); 
    $pedidosCamarero = [];

    foreach ($todosLosPedidos as $p) {
        $estado = $p->getEstado();
        // El camarero ve: recibido, listo cocina, terminado
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





<h2 style="padding: 0 20px;">ESTADO DE LOS PEDIDOS (CAMARERO)</h2>

<?php if (isset($error)): ?>
    <div style="color: red; padding: 10px; margin: 20px; background: #fee; border-radius: 5px;">
        <?= $error ?>
    </div>
<?php endif; ?>

<!-- Grid de pedidos (como el de cocinero) -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px;">
    
    <?php foreach ($pedidosCamarero as $p): 
        $estado = $p->getEstado();
        
        // Color según estado
        $bgColor = match($estado) {
           'recibido' => '#fff3e0',
            'listo cocina' => '#e8f5e9',
            'terminado' => '#e3f2fd',
            default => '#ffffff'
        };
        
        // Botón según estado
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
        
        $colorBoton = match($estado) {
            'recibido' => '#ff9800',
            'listo cocina' => '#4caf50',
            'terminado' => '#2196f3'
        };
    ?>
    
        <div style="border: 1px solid #ddd; border-radius: 8px; background: <?= $bgColor ?>; overflow: hidden;">
            
            <!-- Cabecera del pedido -->
            <div style="padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between;">
                    <h3 style="margin: 0;">Pedido #<?= $p->getNumeroPedido() ?></h3>
                    <span style="background: #333; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em;">
                        <?= $p->getTipo() === 'local' ? 'LOCAL' : 'LLEVAR' ?>
                    </span>
                </div>
                <p style="margin: 5px 0 0 0; color: #666;"><?= date('H:i', strtotime($p->getFechaHora())) ?></p>
            </div>
            
            <!-- Cuerpo del pedido -->
            <div style="padding: 15px;">
                <p>Cliente ID: <?= $p->getIdCliente() ?></p>
                <p><strong><?= number_format($p->getTotal(), 2) ?>€</strong></p>
                <p>Estado: <strong><?= $estado ?></strong></p>
                
                <!-- Botones -->
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <a href="detalle_pedido.php?id=<?= $p->getId() ?>" 
                       style="flex: 1; text-align: center; padding: 8px; background: #666; color: white; text-decoration: none; border-radius: 4px;">
                        VER DETALLE
                    </a>
                    
                    <form method="POST" action="procesar_camarero.php" style="flex: 1;">
                        <input type="hidden" name="id_pedido" value="<?= $p->getId() ?>">
                        <input type="hidden" name="accion" value="<?= $accion ?>">
                        <button type="submit" 
                                style="width: 100%; padding: 8px; background: <?= $colorBoton ?>; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                            <?= $textoBoton ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    <?php endforeach; ?>
    
    <?php if (empty($pedidosCamarero)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: #f9f9f9; border-radius: 8px;">
            <p style="font-size: 1.2em; color: #666;">No hay pedidos que gestionar.</p>
        </div>
    <?php endif; ?>
    
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';