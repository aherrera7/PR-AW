<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/app/sa/PedidoSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$idCliente = $_SESSION['id_usuario'] ?? null; 

if (empty($carrito)) {
    header('Location: categorias_listar.php');
    exit;
}

try {
    $idPedido = PedidoSA::crearDesdeCarrito((int)$idCliente, 'local', $carrito);

    $_SESSION['carrito'] = [];
    
    $_SESSION['mensaje_exito'] = "Pedido #$idPedido realizado correctamente.";
    header('Location: carrito_ver.php');
    exit;

} catch (Exception $e) {
    $_SESSION['errores'] = ["Error al procesar pedido: " . $e->getMessage()];
    header('Location: carrito_ver.php');
    exit;
}