<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once RAIZ_APP . '/includes/vistas/common/auth.php';
requireGerente();

require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

$sa = new UsuarioSA();

$id = (int)($_GET['id'] ?? 0);
$usuario = $sa->getById($id);

if (!$usuario) {
  header('Location: ' . RUTA_APP . '/gerente/usuarios.php');
  exit;
}

$roles = ['cliente','camarero','cocinero','gerente'];
$rolActual = $usuario->getRoles()[0]->getNombre() ?? 'cliente';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nuevoRol = (string)($_POST['rol'] ?? '');

  if (!in_array($nuevoRol, $roles, true)) {
    $errores[] = 'Rol no válido.';
  } else {
    // evitar que te quites el rol gerente a ti mismo
    if (!empty($_SESSION['usuario_id']) && (int)$_SESSION['usuario_id'] === (int)$usuario->getId() && $nuevoRol !== 'gerente') {
      $errores[] = 'No puedes quitarte el rol gerente a ti mismo.';
    } else {
      $sa->cambiarRolUsuario((int)$usuario->getId(), $nuevoRol);
      header('Location: ' . RUTA_APP . '/gerente/usuarios.php');
      exit;
    }
  }
}

$tituloPagina = 'Cambiar rol';

ob_start();
require RAIZ_APP . '/includes/vistas/gerente/cambiar_rol_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla_staff.php';