<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';

if (!isset($_SESSION['login'])) {
    header('Location: ' . RUTA_VISTAS . '/login.php');
    exit;
}

$idOferta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idOferta || $idOferta < 1) {
    $_SESSION['mensaje_exito'] = 'No se pudo añadir la oferta al carrito.';
    header('Location: ' . RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php');
    exit;
}

$oferta = OfertaSA::obtener($idOferta);

if ($oferta === null) {
    $_SESSION['mensaje_exito'] = 'La oferta no existe.';
    header('Location: ' . RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php');
    exit;
}

$lineas = OfertaSA::obtenerProductosOferta($idOferta);

if (empty($lineas)) {
    $_SESSION['mensaje_exito'] = 'La oferta no tiene productos asociados.';
    header('Location: ' . RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php');
    exit;
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

foreach ($lineas as $linea) {
    $idProducto = $linea->getIdProducto();
    $cantidad = $linea->getCantidad();

    if (!isset($_SESSION['carrito'][$idProducto])) {
        $_SESSION['carrito'][$idProducto] = 0;
    }

    $_SESSION['carrito'][$idProducto] += $cantidad;
}

$_SESSION['mensaje_exito'] = 'Se ha añadido la oferta "' . $oferta->getNombre() . '" al carrito.';
$volver = $_SERVER['HTTP_REFERER'] ?? (RUTA_APP . '/includes/vistas/ofertas/ofertas_listar.php');
header('Location: ' . $volver);
exit;