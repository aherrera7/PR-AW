<?php

require_once __DIR__ . '/Aplicacion.php';
require_once __DIR__ . '/app/util/helper.php';

//Definimos rutas globales
define('RAIZ_APP', dirname(__DIR__));
//define('RUTA_APP', '');
define('RUTA_APP', '/PR-AW/BistroFDI');

define('RUTA_VISTAS', RUTA_APP . '/includes/vistas');
define('RUTA_IMGS', RUTA_APP . '/img');
define('RUTA_CSS', RUTA_APP . '/css');
define('RUTA_JS', RUTA_APP . '/js');

ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');

//Acceso a la base de datos
define('BD_HOST', 'localhost');
define('BD_NAME', 'prAW');
define('BD_USER', 'prAW');
define('BD_PASS', 'practicaAW1234'); 
/* 
define('BD_HOST', 'vm014.db.swarm.test');
define('BD_NAME', 'prAW');
define('BD_USER', 'prAW');
define('BD_PASS', 'practicaAW1234'); 
*/

//Inicializamos la aplicación
$app = Aplicacion::getInstance();
$app->init([
    'host' => BD_HOST,
    'bd'   => BD_NAME,
    'user' => BD_USER,
    'pass' => BD_PASS,
]);

register_shutdown_function([$app, 'shutdown']);