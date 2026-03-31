<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

// Verificación de acceso (Cocinero o Gerente)
requireGerenteOCocinero();

$idCocineroActual = (int)$_SESSION['usuario_id'];
$nombreCocineroActual = $_SESSION['nombre'] ?? 'Cocinero';

//Actualización de estado del pedido (asignar a cocinero o marcar como listo cocina)
$idPedido = (int)($_GET['id_pedido'] ?? 0);

if (isset($_GET['asignar']) && $_GET['asignar'] == 1) {
    PedidoSA::asignarCocinero($idPedido, $idCocineroActual);
    header('Location: productos_pedido.php?id_pedido=' . $idPedido);
    exit;
}

if (isset($_GET['finalizar']) && (int)$_GET['finalizar'] > 0) {
    PedidoSA::actualizarEstado($idPedido, PedidoSA::ESTADO_LISTO_COCINA);
    header('Location: pedidos_listar_cocineros.php');
    exit;
}

//Carga de datos para mostrar en la vista
$pedidoDTO = PedidoSA::obtener($idPedido);
if (!$pedidoDTO) {
    die("Pedido no encontrado.");
}

$idCocineroAsignado = $pedidoDTO->getIdCocinero();
$nombreCocineroAsignado = null;
if ($idCocineroAsignado) {
    $usuarioSA = new UsuarioSA();
    $cocinero = $usuarioSA->getById($idCocineroAsignado);
    $nombreCocineroAsignado = $cocinero ? $cocinero->getNombre() : 'Cocinero #' . $idCocineroAsignado;
}

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
    <div class="kitchen-detail-head">
        <a href="pedidos_listar_cocineros.php" class="btn btn-light">← Volver</a>
        <h1 class="mb-0">Comanda #<?= $pedidoDTO->getNumeroPedido() ?></h1>
        <span class="kitchen-badge"><?= strtoupper($pedidoDTO->getTipo()) ?></span>

        <?php if ($idCocineroAsignado): ?>
            <div class="kitchen-assigned">
                <span class="kitchen-assigned-icon">👨‍🍳</span>
                <div>
                    <div class="kitchen-assigned-name">
                        <?= $idCocineroAsignado == $idCocineroActual ? 'Tú' : htmlspecialchars($nombreCocineroAsignado) ?>
                    </div>
                    <div class="kitchen-assigned-label">Cocinero asignado</div>
                </div>
            </div>
        <?php else: ?>
            <div class="kitchen-assigned-wrap">
                <a href="productos_pedido.php?id_pedido=<?= $idPedido ?>&asignar=1" class="btn kitchen-assign-btn">
                    📋 ASIGNARME ESTE PEDIDO
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card kitchen-products-card">
        <table class="kitchen-products-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="cell-center">Cantidad</th>
                    <th class="cell-right">Estado</th>
                </tr>
            </thead>
            <tbody id="lista-productos">
                <?php foreach ($productosMostrar as $item): ?>
                <tr id="fila-<?= $item['id'] ?>" data-estado="pendiente" class="kitchen-product-row">
                    <td class="kitchen-product-main">
                        <img src="<?= h(RUTA_IMGS . '/' . ltrim((string)$item['img'], '/')) ?>"" class="kitchen-product-thumb" alt="<?= h($item['nombre']) ?>">
                        <span class="kitchen-product-name"><?= h($item['nombre']) ?></span>
                    </td>
                    <td class="cell-center kitchen-product-qty">
                        <strong>x<?= $item['cantidad'] ?></strong>
                    </td>
                    <td class="cell-right" id="accion-<?= $item['id'] ?>">
                        <button class="btn btn-success" onclick="marcarListo(<?= $item['id'] ?>)">
                            LISTO
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="kitchen-finish-wrap">
        <button id="btnFinalizarPedido" class="btn kitchen-finish-btn kitchen-finish-btn-disabled" onclick="finalizarPedido(<?= $idPedido ?>)" disabled>
            PEDIDO COMPLETADO
        </button>
    </div>
</section>

<script>
let pendientes = <?= count($productosMostrar) ?>;

function marcarListo(id) {
    const fila = document.getElementById('fila-' + id);
    const celdaAccion = document.getElementById('accion-' + id);
    
    fila.classList.add('kitchen-product-row-ready');
    fila.setAttribute('data-estado', 'listo');
    
    celdaAccion.innerHTML = `
        <span class="kitchen-ready-label">✓ LISTO</span>
        <button class="btn btn-light kitchen-undo-btn" onclick="deshacerListo(${id})">Deshacer</button>
    `;
    
    pendientes--;
    actualizarBotonMaestro();
}

function deshacerListo(id) {
    const fila = document.getElementById('fila-' + id);
    const celdaAccion = document.getElementById('accion-' + id);
    
    fila.classList.remove('kitchen-product-row-ready');
    fila.setAttribute('data-estado', 'pendiente');
    
    celdaAccion.innerHTML = `
        <button class="btn btn-success" onclick="marcarListo(${id})">
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
        btn.classList.remove('kitchen-finish-btn-disabled');
        btn.classList.add('kitchen-finish-btn-enabled');
    } else {
        btn.disabled = true;
        btn.classList.remove('kitchen-finish-btn-enabled');
        btn.classList.add('kitchen-finish-btn-disabled');
    }
}

function finalizarPedido(id) {
    if (confirm('¿Confirmas que toda la comanda está lista para ser servida?')) {
        window.location.href = 'productos_pedido.php?id_pedido=' + id + '&finalizar=' + id;
    }
}
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';