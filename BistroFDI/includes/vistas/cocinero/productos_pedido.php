<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$esGerente = (!empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true);
$esCocinero = (!empty($_SESSION['esCocinero']) && $_SESSION['esCocinero'] === true);

if (!$esGerente && !$esCocinero) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

// --- LÓGICA DE ACTUALIZACIÓN DE ESTADO ---
$idPedido = (int)($_GET['id_pedido'] ?? 0);

if (isset($_GET['finalizar']) && (int)$_GET['finalizar'] > 0) {
    // Aquí actualizamos la base de datos de verdad
    PedidoSA::actualizarEstado($idPedido, PedidoSA::ESTADO_LISTO_COCINA);
    header('Location: pedidos_listar_cocineros.php');
    exit;
}

// --- CARGA DE DATOS REALES ---
$pedidoDTO = PedidoSA::obtener($idPedido);
if (!$pedidoDTO) {
    die("Pedido no encontrado.");
}

// Obtenemos las líneas del pedido (productos, cantidades, etc.)
// Asumo que tu PedidoSA tiene un método para obtener las líneas o usamos el DAO directamente
$lineasDTO = PedidoSA::obtenerDetalle($idPedido); 

$productosMostrar = [];
foreach ($lineasDTO as $linea) {
    $prod = ProductoSA::obtener($linea->getIdProducto());
    if ($prod) {
        $productosMostrar[] = [
            'id' => $prod->getId(),
            'nombre' => $prod->getNombre(),
            'cantidad' => $linea->getCantidad(),
            'img' => ($prod->getImagenes()[0] ?? 'default.jpg')
        ];
    }
}

$tituloPagina = "Preparando Pedido #" . $pedidoDTO->getNumeroPedido();
ob_start();
?>

<section class="ger-wrap">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
        <a href="pedidos_listar_cocineros.php" class="btn-undo" style="text-decoration: none;">← Volver</a>
        <h1 style="margin: 0;">Comanda #<?= $pedidoDTO->getNumeroPedido() ?></h1>
        <span class="badge"><?= strtoupper($pedidoDTO->getTipo()) ?></span>
    </div>

    <div class="card" style="padding: 0;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Producto</th>
                    <th style="padding: 15px; text-align: center;">Cantidad</th>
                    <th style="padding: 15px; text-align: right;">Estado</th>
                </tr>
            </thead>
            <tbody id="lista-productos">
                <?php foreach ($productosMostrar as $item): ?>
                <tr id="fila-<?= $item['id'] ?>" data-estado="pendiente" style="border-bottom: 1px solid #eee; transition: background 0.3s;">
                    <td style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <img src="<?= RUTA_IMGS ?>/productos/<?= $item['img'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <span style="font-weight: bold;"><?= h($item['nombre']) ?></span>
                    </td>
                    <td style="padding: 15px; text-align: center; font-size: 1.2em;">
                        <strong>x<?= $item['cantidad'] ?></strong>
                    </td>
                    <td style="padding: 15px; text-align: right;" id="accion-<?= $item['id'] ?>">
                        <button class="btn" onclick="marcarListo(<?= $item['id'] ?>)" style="background: #2e7d32; color: white;">
                            LISTO
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button id="btnFinalizarPedido" class="btn" onclick="finalizarPedido(<?= $idPedido ?>)" 
                style="padding: 15px 40px; font-size: 1.1em; opacity: 0.5; cursor: not-allowed;" disabled>
            PEDIDO COMPLETADO
        </button>
    </div>
</section>

<script>
let pendientes = <?= count($productosMostrar) ?>;

function marcarListo(id) {
    const fila = document.getElementById('fila-' + id);
    const celdaAccion = document.getElementById('accion-' + id);
    
    fila.style.backgroundColor = "#e8f5e9";
    fila.setAttribute('data-estado', 'listo');
    
    celdaAccion.innerHTML = `
        <span style="color: #2e7d32; font-weight: bold; margin-right: 10px;">✓ LISTO</span>
        <button class="btn-undo" onclick="deshacerListo(${id})">Deshacer</button>
    `;
    
    pendientes--;
    actualizarBotonMaestro();
}

function deshacerListo(id) {
    const fila = document.getElementById('fila-' + id);
    const celdaAccion = document.getElementById('accion-' + id);
    
    fila.style.backgroundColor = "white";
    fila.setAttribute('data-estado', 'pendiente');
    
    celdaAccion.innerHTML = `
        <button class="btn" onclick="marcarListo(${id})" style="background: #2e7d32; color: white;">
            LISTO
        </button>
    `;
    
    pendientes++;
    actualizarBotonMaestro();
}

function actualizarBotonMaestro() {
    const btn = document.getElementById('btnFinalizarPedido');
    if (pendientes === 0) {
        btn.disabled = false;
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
        btn.style.backgroundColor = "#d32f2f";
    } else {
        btn.disabled = true;
        btn.style.opacity = "0.5";
        btn.style.cursor = "not-allowed";
        btn.style.backgroundColor = "#333";
    }
}

function finalizarPedido(id) {
    if (confirm('¿Confirmas que toda la comanda está lista para ser servida?')) {
        window.location.href = 'productos_pedido.php?id_pedido=' + id + '&finalizar=' + id;
    }
}
</script>

<style>
    .btn-undo { background: #eee; color: #333; border: 1px solid #ccc; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
    .badge { background: #333; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8em; }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';