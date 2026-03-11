<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
// IMPORTANTE: Asegúrate de que tu Login guarda el ID en $_SESSION['id_usuario']
$idCliente = $_SESSION['id_usuario'] ?? null; 

if (empty($carrito)) {
    header('Location: categorias_listar.php');
    exit;
}

try {
    // 1. Llamamos al SA para crear el pedido real en la BD
    // Esto ejecutará insertPedido e insertLineaPedido en cadena
    $idPedido = PedidoSA::crearDesdeCarrito((int)$idCliente, 'local', $carrito);

    // 2. Limpiamos el carrito si la inserción fue exitosa
    $_SESSION['carrito'] = [];
    
    // 3. Mensaje para la vista
    $_SESSION['mensaje_exito'] = "Pedido #$idPedido realizado correctamente.";
    header('Location: carrito_ver.php');
    exit;

} catch (Exception $e) {
    $_SESSION['errores'] = ["Error al procesar pedido: " . $e->getMessage()];
    header('Location: carrito_ver.php');
    exit;
}