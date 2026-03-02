<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';

$app = Aplicacion::getInstance();

$mensaje    = $app->getAtributoPeticion('msg');
$categorias = CategoriaSA::listar();

$tituloPagina = 'Gestión de Categorías';

ob_start();
require RAIZ_APP . '/includes/vistas/gerente/categorias_listar_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';