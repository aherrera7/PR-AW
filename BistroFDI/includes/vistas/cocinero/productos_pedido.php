<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';


// --- MEJORA DE SEGURIDAD Y MENSAJES ---
if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

// Verificamos si es Gerente o Cocinero (ajusta 'cocinero' según tu BD, p.ej. si es ID 2 o el string 'cocinero')
$esGerente = (!empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true);
$esCocinero = (!empty($_SESSION['esCocinero']) && $_SESSION['esCocinero'] === true);

if (!$esGerente && !$esCocinero) {
    // Si está logueado pero no tiene permisos, le mandamos al index con un aviso
    $_SESSION['errores'] = ["No tienes permisos para acceder a la sección de cocina."];
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

// --- DATOS SIMULADOS ---
$idPedido = (int)($_GET['id_pedido'] ?? 0);
$productosPedido = [
    ['id' => 101, 'nombre' => 'Hamburguesa Especial FDI', 'img' => 'hamb_1.jpg', 'estado' => 'pendiente'],
    ['id' => 102, 'nombre' => 'Ración Patatas Grandes', 'img' => 'patatas_1.jpg', 'estado' => 'pendiente'],
    ['id' => 103, 'nombre' => 'Refresco de Cola 500ml', 'img' => 'coca_1.jpg', 'estado' => 'listo'],
];

$tituloPagina = "Cocina - Pedido #$idPedido";
ob_start();
?>

<section class="ger-wrap">
    <div class="card stack" style="margin-bottom: 20px; border-left: 5px solid #2e7d32;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="margin: 0;">Preparando Pedido #<?= $idPedido ?></h1>
                <p class="muted">Panel de Control de Cocina</p>
            </div>
            <button id="btnFinalizarPedido" class="btn" style="opacity: 0.5; cursor: not-allowed;" onclick="cambiarEstadoPedido(<?= $idPedido ?>)" disabled>
                Pedido Completo
            </button>
        </div>
    </div>

    <div class="stack" style="gap: 8px;">
        <?php foreach ($productosPedido as $prod): ?>
            <div class="card prod-row" id="fila-<?= $prod['id'] ?>" 
                 data-estado="<?= $prod['estado'] ?>"
                 style="display: flex; align-items: center; padding: 10px 20px; gap: 15px; transition: all 0.3s;">
                
                <img src="<?= h(RUTA_IMGS.'/productos/'.$prod['img']) ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 4px;">
                
                <div style="flex: 1;">
                    <span style="font-weight: 600;"><?= h($prod['nombre']) ?></span>
                </div>

                <div id="accion-<?= $prod['id'] ?>" style="display: flex; align-items: center; gap: 10px;">
                    <?php if ($prod['estado'] === 'listo'): ?>
                        <span style="color: #2e7d32; font-weight: bold;">✓ LISTO</span>
                        <button class="btn-undo" onclick="deshacerListo(<?= $prod['id'] ?>)">↩</button>
                    <?php else: ?>
                        <button class="btn btn-light" onclick="marcarListo(<?= $prod['id'] ?>)" style="border-color: #2e7d32; color: #2e7d32;">
                            LISTO
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
let pendientes = document.querySelectorAll('.prod-row[data-estado="pendiente"]').length;

function marcarListo(id) {
    const contenedor = document.getElementById('accion-' + id);
    const fila = document.getElementById('fila-' + id);
    
    // Cambiamos el HTML para mostrar el check y el botón deshacer
    contenedor.innerHTML = `
        <span style="color: #2e7d32; font-weight: bold;">✓ LISTO</span>
        <button class="btn-undo" onclick="deshacerListo(${id})">↩</button>
    `;
    
    fila.style.backgroundColor = "#f0f9f0";
    fila.setAttribute('data-estado', 'listo');
    
    pendientes--;
    actualizarBotonMaestro();
}

function deshacerListo(id) {
    const contenedor = document.getElementById('accion-' + id);
    const fila = document.getElementById('fila-' + id);
    
    // Volvemos al botón original
    contenedor.innerHTML = `
        <button class="btn btn-light" onclick="marcarListo(${id})" style="border-color: #2e7d32; color: #2e7d32;">
            LISTO
        </button>
    `;
    
    fila.style.backgroundColor = "white";
    fila.setAttribute('data-estado', 'pendiente');
    
    pendientes++;
    actualizarBotonMaestro();
}

function actualizarBotonMaestro() {
    const btn = document.getElementById('btnFinalizarPedido');
    if (pendientes === 0) {
        btn.disabled = false;
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
        btn.style.backgroundColor = "#2e7d32";
    } else {
        btn.disabled = true;
        btn.style.opacity = "0.5";
        btn.style.cursor = "not-allowed";
        btn.style.backgroundColor = ""; // Vuelve al color por defecto de .btn
    }
}

function cambiarEstadoPedido(id) {
    if (confirm('¿Confirmas que el pedido está terminado?')) {
        window.location.href = 'pedidos_listar_cocineros.php?finalizado=' + id;
    }
}

// Inicialización
actualizarBotonMaestro();
</script>

<style>
    .btn-undo {
        background: #eee;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
        padding: 2px 8px;
        font-size: 1.1em;
    }
    .btn-undo:hover { background: #ddd; }
    .prod-row { border: 1px solid #eee; margin-bottom: 5px; }
</style>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';