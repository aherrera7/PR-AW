<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$tituloPagina = 'Mi Carrito';
$carrito = $_SESSION['carrito'] ?? [];
$productosCarrito = [];
$total = 0;
$errores = [];

// 1. LÓGICA DE PROCESAMIENTO DEL FORMULARIO (Cuando se pulsa "Confirmar")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    try {
        if (empty($_SESSION['carrito'])) throw new InvalidArgumentException("El carrito está vacío.");
        
        // Obtenemos el tipo elegido (local o llevar)
        $tipoPedido = $_POST['pedido_tipo'] ?? null;
        if (!$tipoPedido) throw new InvalidArgumentException("Debes elegir si el pedido es para tomar aquí o para llevar.");

        // Obtenemos el ID del usuario (ajusta la clave si en tu login es distinta)
        $idCliente = (int)($_SESSION['usuario_id'] ?? 0); 

        // Creamos el pedido real en la BD usando tu SA
        $idPedido = PedidoSA::crearDesdeCarrito(
            $idCliente,
            $tipoPedido,
            $_SESSION['carrito']
        );

        // Si ha ido bien, vaciamos carrito y redirigimos
        unset($_SESSION['carrito']);
        $_SESSION['mensaje_exito'] = "¡Pedido #$idPedido realizado con éxito!";
        header("Location: " . RUTA_APP . "/index.php"); // O a una vista de "Mis Pedidos"
        exit;

    } catch (Throwable $e) {
        $errores[] = $e->getMessage();
    }
}

// 2. CARGA DE DATOS PARA MOSTRAR EL CARRITO
foreach ($carrito as $id => $cantidad) {
    $p = ProductoSA::obtener((int)$id);
    if ($p) {
        $subtotal = $p->getPrecioFinal() * $cantidad;
        $total += $subtotal;
        $productosCarrito[] = [
            'obj' => $p,
            'cantidad' => $cantidad,
            'subtotal' => $subtotal
        ];
    }
}

ob_start();
?>

<section class="ger-wrap">
    <h1>Mi Carrito</h1>

    <?php if (!empty($errores)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php foreach ($errores as $e) echo "<p style='margin:0;'>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($productosCarrito)): ?>
        <div class="card" style="text-align: center; padding: 50px;">
            <p>Tu carrito está actualmente vacío.</p>
            <a href="categorias_listar.php" class="btn">Ver la carta</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">
            
            <div style="flex: 2; min-width: 300px;">
                <div class="stack" style="gap: 10px;">
                    <?php foreach ($productosCarrito as $item): 
                        $prod = $item['obj'];
                        $img = $prod->getImagenes()[0] ?? 'default.jpg';
                    ?>
                        <div class="card" style="display: flex; align-items: center; gap: 15px; padding: 10px;">
                            <img src="<?= RUTA_IMGS ?>/productos/<?= $img ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0;"><?= h($prod->getNombre()) ?></h4>
                                <small class="muted"><?= number_format($prod->getPrecioFinal(), 2) ?>€ / ud.</small>
                            </div>
                            <div style="text-align: center;">
                                <span>x<?= $item['cantidad'] ?></span>
                            </div>
                            <div style="min-width: 80px; text-align: right; font-weight: bold;">
                                <?= number_format($item['subtotal'], 2) ?>€
                            </div>
                            <a href="carrito_gestion.php?action=remove&id=<?= $prod->getId() ?>" style="color:red; text-decoration:none; padding: 5px;">✕</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div class="card stack" style="padding: 20px; position: sticky; top: 20px;">
                    <h3 style="margin-top: 0;">Resumen del pedido</h3>
                    
                    <form method="post">
                        <p style="font-weight: bold; margin-bottom: 10px; font-size: 0.9em;">¿Cómo quieres tu pedido?</p>
                        <div style="display: flex; gap: 10px; margin-bottom: 25px;">
                            <label style="flex: 1;">
                                <input type="radio" name="pedido_tipo" value="local" required style="display: none;" class="radio-tipo">
                                <div class="tipo-btn">
                                    <span style="font-size: 1.5em;">🏠</span><br>Tomar aquí
                                </div>
                            </label>
                            <label style="flex: 1;">
                                <input type="radio" name="pedido_tipo" value="llevar" required style="display: none;" class="radio-tipo">
                                <div class="tipo-btn">
                                    <span style="font-size: 1.5em;">🥡</span><br>Para llevar
                                </div>
                            </label>
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                            <span>Subtotal (sin IVA):</span>
                            <span><?= number_format($total / 1.10, 2) ?>€</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                            <span>IVA (10%):</span>
                            <span><?= number_format($total - ($total / 1.10), 2) ?>€</span>
                        </div>
                        <hr>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.3em;">
                            <span>Total:</span>
                            <span style="color: #d32f2f;"><?= number_format($total, 2) ?>€</span>
                        </div>

                        <button type="submit" name="confirmar" class="btn" style="width: 100%; margin-top: 20px; padding: 12px;">
                            CONFIRMAR Y PAGAR
                        </button>
                    </form>

                    <a href="carrito_gestion.php?action=clear" class="muted" style="display: block; text-align: center; margin-top: 15px; font-size: 0.8em;">Vaciar carrito</a>
                </div>
            </div>

        </div>
    <?php endif; ?>
</section>

<style>
    .tipo-btn {
        border: 2px solid #eee;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        background: #fafafa;
        transition: all 0.2s;
        font-size: 0.85em;
    }
    .tipo-btn:hover { background: #f0f0f0; border-color: #ccc; }

    /* Estilo cuando el radio invisible está seleccionado */
    .radio-tipo:checked + .tipo-btn {
        border-color: #d32f2f;
        background: #fff5f5;
        color: #d32f2f;
        font-weight: bold;
    }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';