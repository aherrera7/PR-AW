<?php
require_once dirname(__DIR__) . '/config.php';

ob_start();
?>

<h2>Contenido de la página</h2>
<p>Hola mundo</p>

<?php
$contenido = ob_get_clean();
require RAIZ_APP . '/includes/plantilla.php';