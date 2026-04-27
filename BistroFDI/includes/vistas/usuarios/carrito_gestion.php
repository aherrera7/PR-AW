<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$cant = (int)($_GET['cant'] ?? 1);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

switch ($action) {
    case 'add':
        if ($id > 0) {
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id] += $cant;
            } else {
                $_SESSION['carrito'][$id] = $cant;
            }
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        break;

    case 'remove':
        if ($id > 0) {
            unset($_SESSION['carrito'][$id]);
        }
        header('Location: carrito_ver.php');
        exit;

    case 'clear':
        unset($_SESSION['carrito']);
        header('Location: carrito_ver.php');
        exit;

    case 'update':
        if ($id > 0) {
            if ($cant > 0) {
                $_SESSION['carrito'][$id] = $cant;
            } else {
                unset($_SESSION['carrito'][$id]);
            }
        }
        // Si la petición es AJAX, respondemos con JSON, si no, redirigimos
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['status' => 'ok']);
            exit;
        }
        header('Location: carrito_ver.php');
        exit;

    default:
        header('Location: ../../index.php');
        exit;
}