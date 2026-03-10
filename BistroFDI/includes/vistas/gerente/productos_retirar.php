<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente(); // Solo el gerente puede hacer esto

require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

$app = Aplicacion::getInstance();
$id = (int)($_GET['id'] ?? 0);
$idCat = (int)($_GET['id_cat'] ?? 0);

if ($id > 0) {
    try {
        $producto = ProductoSA::obtener($id);
        if ($producto) {
            if ($producto->isOfertado()) {
                ProductoSA::retirarDeCarta($id);
                $msg = "Producto retirado con éxito.";
            } else {
                ProductoSA::ponerEnCarta($id);
                $msg = "Producto reofertado con éxito.";
            }
            $app->putAtributoPeticion('msg', $msg);
        }
    } catch (Throwable $e) {
        $app->putAtributoPeticion('msg', "Error: " . $e->getMessage());
    }
}

// LA CLAVE: Redirigir de vuelta a la carta de la categoría correspondiente
header("Location: " . RUTA_APP . "/includes/vistas/usuarios/productos_carta.php?id_cat=" . $idCat);
exit;