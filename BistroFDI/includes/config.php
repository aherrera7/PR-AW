<?php

require_once __DIR__ . '/Aplicacion.php';
require_once __DIR__ . '/app/util/helper.php';

//Conf general
define('RAIZ_APP', dirname(__DIR__));
define('RUTA_APP', '/PR-AW/BistroFDI');

define('RUTA_VISTAS', RUTA_APP . '/includes/vistas');
define('RUTA_IMGS', RUTA_APP . '/img');
define('RUTA_CSS', RUTA_APP . '/css');
define('RUTA_JS', RUTA_APP . '/js');

ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');


//Configura BD
define('BD_HOST', 'localhost');
define('BD_NAME', 'bistrofdi');
define('BD_USER', 'root');
define('BD_PASS', ''); // En XAMPP normalmente vacío

//inicializa app
$app = Aplicacion::getInstance();
$app->init([
    'host' => BD_HOST,
    'bd'   => BD_NAME,
    'user' => BD_USER,
    'pass' => BD_PASS,
]);

// Cerrar conexión automáticamente al finalizar el script
register_shutdown_function([$app, 'shutdown']);