<?php
require_once __DIR__.'/includes/config.php';

//Titulo
$tituloPagina = 'Detalles - Bistro FDI';

ob_start();
require_once RAIZ_APP . '/includes/vistas/detalles_vista.php';
$contenidoPrincipal = ob_get_clean();

require_once RAIZ_APP . '/includes/vistas/common/plantilla.php';