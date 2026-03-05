<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

class FormularioRegistro extends FormularioBase {
    public function __construct() {
        parent::__construct('formRegistro', [
            'urlRedireccion' => RUTA_APP . '/index.php',
            'enctype' => 'multipart/form-data'
        ]);
    }

    protected function generaCamposFormulario(array &$datos): string {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';
        $email = $datos['email'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';

        $avatarElegido = $datos['avatar_predef'] ?? 'avatares/default.jpg';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['nombreUsuario','email','nombre','apellidos','password','password2'],
            $this->errores
        );

        $nombreUsuarioEsc = htmlspecialchars($nombreUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $emailEsc = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $nombreEsc = htmlspecialchars($nombre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $apellidosEsc = htmlspecialchars($apellidos, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $avatarPreviewUrl = RUTA_IMGS . '/' . ltrim($avatarElegido, '/');
        $avatarPreviewUrlEsc = htmlspecialchars($avatarPreviewUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $imgBase = RUTA_IMGS;
        $imgBaseEsc = htmlspecialchars($imgBase, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $opciones = [
            'avatares/default.png' => 'Por defecto',
            'avatares/a1.png' => 'Avatar 1',
            'avatares/a2.png' => 'Avatar 2',
            'avatares/a3.png' => 'Avatar 3',
            'avatares/a4.png' => 'Avatar 4',
        ];

        $optionsHtml = '';
        foreach ($opciones as $value => $label) {
            $sel = ($value === $avatarElegido) ? 'selected' : '';
            $vEsc = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $lEsc = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $optionsHtml .= "<option value=\"{$vEsc}\" {$sel}>{$lEsc}</option>";
        }

return <<<HTML
{$htmlErroresGlobales}

<fieldset>
  <legend>Registro</legend>

  <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-top:10px;">
    <img id="avatarPreview" src="{$avatarPreviewUrlEsc}" alt="Avatar"
         style="width:100px;height:100px;border-radius:50%;border:1px solid #111;object-fit:cover;background:#fff;">

    <div class="stack" style="min-width:260px; flex:1;">
      <div>
        <label for="avatarSelect">Avatares</label>
        <select id="avatarSelect" name="avatar_predef">
          {$optionsHtml}
        </select>
      </div>

      <div>
        <label for="avatarFile">Subir imagen</label>
        <input id="avatarFile" type="file" name="avatar_file" accept="image/*">
      </div>
    </div>
  </div>

  <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-top:12px;">
    <div>
      <label>Usuario</label>
      <input type="text" name="nombreUsuario" value="{$nombreUsuarioEsc}" required>
      {$erroresCampos['nombreUsuario']}
    </div>

    <div>
      <label>Email</label>
      <input type="email" name="email" value="{$emailEsc}" required>
      {$erroresCampos['email']}
    </div>

    <div>
      <label>Nombre</label>
      <input type="text" name="nombre" value="{$nombreEsc}" required>
      {$erroresCampos['nombre']}
    </div>

    <div>
      <label>Apellidos</label>
      <input type="text" name="apellidos" value="{$apellidosEsc}" required>
      {$erroresCampos['apellidos']}
    </div>

    <div>
      <label>Contraseña</label>
      <input type="password" name="password" required>
      {$erroresCampos['password']}
    </div>

    <div>
      <label>Repite contraseña</label>
      <input type="password" name="password2" required>
      {$erroresCampos['password2']}
    </div>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit" name="registro">Crear cuenta</button>
  </div>

  <script>
    (function() {
      const IMG_BASE = "{$imgBaseEsc}";
      const img = document.getElementById("avatarPreview");
      const sel = document.getElementById("avatarSelect");
      const file = document.getElementById("avatarFile");
      if (!img) return;

      if (sel) {
        sel.addEventListener("change", function() {
          const val = sel.value || "avatares/default.jpg";
          img.src = IMG_BASE + "/" + val.replace(/^\\/+/, "");
        });
      }

      if (file) {
        file.addEventListener("change", function() {
          const f = file.files && file.files[0];
          if (!f) return;
          img.src = URL.createObjectURL(f);
        });
      }
    })();
  </script>
</fieldset>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $this->errores = [];

        $nombreUsuario = filter_var(trim($datos['nombreUsuario'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_var(trim($datos['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $nombre = filter_var(trim($datos['nombre'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $apellidos = filter_var(trim($datos['apellidos'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $password = trim($datos['password'] ?? '');
        $password2 = trim($datos['password2'] ?? '');

        $avatarPredef = $datos['avatar_predef'] ?? 'avatares/default.png';

        if (!$nombreUsuario || mb_strlen($nombreUsuario) < 4) $this->errores['nombreUsuario'] = 'Mínimo 4 caracteres.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $this->errores['email'] = 'Email no válido.';
        if (!$nombre) $this->errores['nombre'] = 'Nombre obligatorio.';
        if (!$apellidos) $this->errores['apellidos'] = 'Apellidos obligatorios.';
        if (!$password || mb_strlen($password) < 4) $this->errores['password'] = 'Mínimo 4 caracteres.';
        if (!$password2 || $password !== $password2) $this->errores['password2'] = 'Las contraseñas no coinciden.';

        $avatarFinal = $avatarPredef;

        if (!empty($_FILES['avatar_file']) && isset($_FILES['avatar_file']['tmp_name']) && is_uploaded_file($_FILES['avatar_file']['tmp_name'])) {
            $f = $_FILES['avatar_file'];

            if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $this->errores[] = 'Error al subir la imagen.';
            } else {
                if (($f['size'] ?? 0) > 2 * 1024 * 1024) {
                    $this->errores[] = 'La imagen supera 2MB.';
                } else {
                    $mime = mime_content_type($f['tmp_name']);
                    $permitidos = ['image/png','image/jpeg','image/webp','image/gif'];
                    if (!in_array($mime, $permitidos, true)) {
                        $this->errores[] = 'Formato no permitido (PNG/JPG/WEBP/GIF).';
                    }
                }
            }

            if (count($this->errores) === 0) {
                $ext = pathinfo($f['name'] ?? '', PATHINFO_EXTENSION);
                $ext = $ext ? strtolower($ext) : 'png';

                $nombreArchivo = 'avatares/u_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $destinoAbs = RAIZ_APP . '/img/' . $nombreArchivo;

                if (!move_uploaded_file($f['tmp_name'], $destinoAbs)) {
                    $this->errores[] = 'No se pudo guardar la imagen subida.';
                } else {
                    $avatarFinal = $nombreArchivo; 
                }
            }
        }

        if (count($this->errores) > 0) return;

        try {
            $sa = new UsuarioSA();
            $usuario = $sa->registrarClienteConAvatar($nombreUsuario, $email, $password, $nombre, $apellidos, $avatarFinal);

            if (!$usuario) {
                $this->errores[] = 'No se pudo registrar (usuario ya existe o error).';
                return;
            }

            $_SESSION['login'] = true;
            $_SESSION['usuario_id'] = $usuario->getId();
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            $_SESSION['nombre'] = $usuario->getNombre();
            $_SESSION['avatar'] = $usuario->getAvatar();
            $_SESSION['roles'] = array_map(fn($r) => $r->getNombre(), $usuario->getRoles());

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->errores[] = 'Error interno al registrar.';
        }
    }
}