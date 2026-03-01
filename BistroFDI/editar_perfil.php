<?php
require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
  header('Location: ' . RUTA_APP . '/login.php');
  exit;
}

$sa = new UsuarioSA();
$usuario = $sa->getById((int)$_SESSION['usuario_id']);
if (!$usuario) {
  header('Location: ' . RUTA_APP . '/logout.php');
  exit;
}

$errores = [];

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$avatarActual = $usuario->getAvatar() ?: 'avatares/default.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombreUsuario = trim($_POST['nombreUsuario'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $nombre = trim($_POST['nombre'] ?? '');
  $apellidos = trim($_POST['apellidos'] ?? '');
  $avatarPredef = $_POST['avatar_predef'] ?? $avatarActual;

  if (mb_strlen($nombreUsuario) < 4) $errores[] = 'Usuario mínimo 4 caracteres.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido.';
  if ($nombre === '') $errores[] = 'Nombre obligatorio.';
  if ($apellidos === '') $errores[] = 'Apellidos obligatorios.';

  $avatarFinal = $avatarPredef;

  // opcional: subir imagen
  if (!empty($_FILES['avatar_file']) && is_uploaded_file($_FILES['avatar_file']['tmp_name'])) {
    $f = $_FILES['avatar_file'];
    if (($f['size'] ?? 0) > 2 * 1024 * 1024) $errores[] = 'La imagen supera 2MB.';
    $mime = mime_content_type($f['tmp_name']);
    $permitidos = ['image/png','image/jpeg','image/webp','image/gif'];
    if (!in_array($mime, $permitidos, true)) $errores[] = 'Formato no permitido (PNG/JPG/WEBP/GIF).';

    if (!$errores) {
      $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION) ?: 'png');
      $nombreArchivo = 'avatares/u_' . bin2hex(random_bytes(8)) . '.' . $ext;
      $destinoAbs = RAIZ_APP . '/img/' . $nombreArchivo;
      if (!move_uploaded_file($f['tmp_name'], $destinoAbs)) $errores[] = 'No se pudo guardar la imagen.';
      else $avatarFinal = $nombreArchivo;
    }
  }

  if (!$errores) {
    $nuevo = $sa->actualizarPerfil((int)$usuario->getId(), $nombreUsuario, $email, $nombre, $apellidos, $avatarFinal);
    if (!$nuevo) {
      $errores[] = 'No se pudo actualizar (¿usuario ya existe?).';
    } else {
      // refresca sesión para nav
      $_SESSION['nombre_usuario'] = $nuevo->getNombreUsuario();
      $_SESSION['nombre'] = $nuevo->getNombre();
      $_SESSION['avatar'] = $nuevo->getAvatar();

      header('Location: ' . RUTA_APP . '/mi_perfil.php');
      exit;
    }
  }
}

$opciones = [
  'avatares/default.png' => 'Por defecto',
  'avatares/a1.png' => 'Avatar 1',
  'avatares/a2.png' => 'Avatar 2',
  'avatares/a3.png' => 'Avatar 3',
  'avatares/a4.png' => 'Avatar 4',
];

$optionsHtml = '';
foreach ($opciones as $value => $label) {
  $sel = ($value === $avatarActual) ? 'selected' : '';
  $optionsHtml .= '<option value="'.h($value).'" '.$sel.'>'.h($label).'</option>';
}

$avatarUrl = RUTA_IMGS . '/' . ltrim($avatarActual, '/');

$tituloPagina = 'Editar perfil';

$erroresHtml = '';
if ($errores) {
  $lis = '';
  foreach ($errores as $e) $lis .= '<li>'.h($e).'</li>';
  $erroresHtml = '<ul class="errores">'.$lis.'</ul>';
}

$contenidoPrincipal = '
<section id="contenido">
  <h2>Editar perfil</h2>
  '.$erroresHtml.'

  <form method="post" enctype="multipart/form-data">
    <div class="reg-avatar-row">
      <div class="reg-avatar-wrap">
        <img id="avatarPreview" src="'.h($avatarUrl).'" alt="Avatar">
      </div>

      <div class="reg-avatar-controls">
        <div class="reg-line">
          <label for="avatarSelect">Avatares</label>
          <select id="avatarSelect" name="avatar_predef">
            '.$optionsHtml.'
          </select>
        </div>

        <div class="reg-line">
          <label for="avatarFile">Subir</label>
          <input id="avatarFile" type="file" name="avatar_file" accept="image/*">
        </div>
      </div>
    </div>

    <div class="reg-grid">
      <div class="reg-field">
        <label>Usuario</label>
        <input type="text" name="nombreUsuario" value="'.h($usuario->getNombreUsuario()).'" required>
      </div>

      <div class="reg-field">
        <label>Email</label>
        <input type="email" name="email" value="'.h($usuario->getEmail()).'" required>
      </div>

      <div class="reg-field">
        <label>Nombre</label>
        <input type="text" name="nombre" value="'.h($usuario->getNombre()).'" required>
      </div>

      <div class="reg-field">
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="'.h($usuario->getApellidos()).'" required>
      </div>
    </div>

    <div class="reg-submit">
      <button type="submit">Guardar cambios</button>
      <a class="perfil-btn" href="'.RUTA_APP.'/mi_perfil.php" style="margin-left:10px;">Cancelar</a>
    </div>

    <script>
      (function(){
        const IMG_BASE = "'.h(RUTA_IMGS).'";
        const img = document.getElementById("avatarPreview");
        const sel = document.getElementById("avatarSelect");
        const file = document.getElementById("avatarFile");

        if(sel){
          sel.addEventListener("change", function(){
            img.src = IMG_BASE + "/" + sel.value;
          });
        }
        if(file){
          file.addEventListener("change", function(){
            const f = file.files[0];
            if(f) img.src = URL.createObjectURL(f);
          });
        }
      })();
    </script>

  </form>
</section>';

require RAIZ_APP . '/includes/vistas/common/plantilla.php';