<?php
require_once __DIR__.'/includes/config.php';

//Titulo
$tituloPagina = 'Bocetos - Bistro FDI';

ob_start();
require_once RAIZ_APP . '/includes/vistas/bocetos_vista.php';
$contenidoPrincipal = ob_get_clean();

require_once RAIZ_APP . '/includes/vistas/common/plantilla.php';