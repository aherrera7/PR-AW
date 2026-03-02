<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/Aplicacion.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

$app = Aplicacion::getInstance();
$sa  = new UsuarioSA();

// POST: borrar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_id'])) {
    $idBorrar = (int)$_POST['borrar_id'];

    // evitar que el gerente se borre a sí mismo
    if (!empty($_SESSION['usuario_id']) && $idBorrar === (int)$_SESSION['usuario_id']) {
        $app->putAtributoPeticion('msg', 'No puedes borrarte a ti mismo.');
    } else {
        $sa->borrarUsuario($idBorrar);
        $app->putAtributoPeticion('msg', 'Usuario eliminado.');
    }

    header('Location: ' . RUTA_APP . '/gerente/usuarios.php');
    exit;
}

$usuarios = $sa->listarUsuarios();
$flash    = $app->getAtributoPeticion('msg');

$tituloPagina = 'Usuarios';

ob_start();
require RAIZ_APP . '/includes/vistas/gerente/usuarios_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';