<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
  header('Location: ' . RUTA_APP . '/login.php');
  exit;
}

$sa = new UsuarioSA();

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

$idObjetivo = (int)($_SESSION['usuario_id']);
if ($esGerente && isset($_GET['id'])) $idObjetivo = (int)$_GET['id'];
if (!$esGerente && isset($_GET['id'])) $idObjetivo = (int)($_SESSION['usuario_id']);

$editandoPropio = ($idObjetivo === (int)$_SESSION['usuario_id']);

$usuario = $sa->getById($idObjetivo);
if (!$usuario) {
  header('Location: ' . RUTA_APP . '/logout.php');
  exit;
}

$errores = [];

$avatarActual = $usuario->getAvatar() ?: 'avatares/default.jpg';

$opciones = [
  'avatares/default.jpg' => 'Por defecto',
  'avatares/a1.png' => 'Avatar 1',
  'avatares/a2.png' => 'Avatar 2',
  'avatares/a3.png' => 'Avatar 3',
  'avatares/a4.png' => 'Avatar 4',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombreUsuario = trim((string)($_POST['nombreUsuario'] ?? ''));
  $email         = trim((string)($_POST['email'] ?? ''));
  $nombre        = trim((string)($_POST['nombre'] ?? ''));
  $apellidos     = trim((string)($_POST['apellidos'] ?? ''));
  $avatarPredef  = (string)($_POST['avatar_predef'] ?? $avatarActual);

  if (mb_strlen($nombreUsuario) < 4) $errores[] = 'Usuario mínimo 4 caracteres.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido.';
  if ($nombre === '') $errores[] = 'Nombre obligatorio.';
  if ($apellidos === '') $errores[] = 'Apellidos obligatorios.';

  $avatarFinal = $avatarPredef;

  if (!empty($_FILES['avatar_file']) && is_uploaded_file($_FILES['avatar_file']['tmp_name'])) {
    $f = $_FILES['avatar_file'];

    if (($f['size'] ?? 0) > 2 * 1024 * 1024) $errores[] = 'La imagen supera 2MB.';
    $mime = mime_content_type($f['tmp_name']);
    $permitidos = ['image/png','image/jpeg','image/webp','image/gif'];
    if (!in_array($mime, $permitidos, true)) $errores[] = 'Formato no permitido (PNG/JPG/WEBP/GIF).';

    if (!$errores) {
      $ext = strtolower(pathinfo((string)($f['name'] ?? ''), PATHINFO_EXTENSION) ?: 'png');
      $nombreArchivo = 'avatares/u_' . bin2hex(random_bytes(8)) . '.' . $ext;
      $destinoAbs = RAIZ_APP . '/img/' . $nombreArchivo;

      if (!move_uploaded_file($f['tmp_name'], $destinoAbs)) {
        $errores[] = 'No se pudo guardar la imagen.';
      } else {
        $avatarFinal = $nombreArchivo;
      }
    }
  }

  if (!$errores) {
    $nuevo = $sa->actualizarPerfil((int)$usuario->getId(), $nombreUsuario, $email, $nombre, $apellidos, $avatarFinal);
    if (!$nuevo) {
      $errores[] = 'No se pudo actualizar (¿usuario ya existe?).';
    } else {
      if ($editandoPropio) {
        $_SESSION['nombre_usuario'] = $nuevo->getNombreUsuario();
        $_SESSION['nombre'] = $nuevo->getNombre();
        $_SESSION['avatar'] = $nuevo->getAvatar();
      }

      if ($esGerente && !$editandoPropio) {
        header('Location: ' . RUTA_APP . '/gerente/usuarios.php');
      } else {
        header('Location: ' . RUTA_APP . '/mi_perfil.php');
      }
      exit;
    }
  }

  // Si hubo errores y el usuario cambió selección, mantenemos preview:
  $avatarActual = $avatarFinal;
}

$avatarUrl = RUTA_IMGS . '/' . ltrim($avatarActual, '/');

$tituloPagina   = ($esGerente && !$editandoPropio) ? 'Editar usuario' : 'Editar perfil';
$btnCancelarUrl = ($esGerente && !$editandoPropio) ? (RUTA_APP . '/gerente/usuarios.php') : (RUTA_APP . '/mi_perfil.php');

ob_start();
require RAIZ_APP . '/includes/vistas/editar_perfil_vista.php';
$contenidoPrincipal = ob_get_clean();

require RAIZ_APP . '/includes/vistas/common/plantilla.php';