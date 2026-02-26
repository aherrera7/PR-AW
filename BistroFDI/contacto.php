<?php
require_once __DIR__.'/includes/config.php';

//Titulo
$tituloPagina = 'Contacto - Bistro FDI';

ob_start();
require_once RAIZ_APP . '/includes/vistas/contacto_vista.php';
$contenidoPrincipal = ob_get_clean();

require_once RAIZ_APP . '/includes/vistas/common/plantilla.php';