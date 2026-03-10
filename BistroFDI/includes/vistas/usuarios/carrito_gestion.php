<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';

// Iniciamos la sesión para poder manipular el carrito
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$cant = (int)($_GET['cant'] ?? 1);

// Inicializamos el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

switch ($action) {
    case 'add':
        if ($id > 0) {
            // Si el producto ya está, sumamos la cantidad; si no, lo creamos
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id] += $cant;
            } else {
                $_SESSION['carrito'][$id] = $cant;
            }
            // Al añadir, volvemos a la carta de productos
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        }
        break;

    case 'remove':
        if ($id > 0) {
            unset($_SESSION['carrito'][$id]);
        }
        header("Location: carrito_ver.php");
        break;

    case 'clear':
        unset($_SESSION['carrito']);
        header("Location: carrito_ver.php");
        break;

    default:
        header("Location: ../../index.php");
        break;
}
exit;