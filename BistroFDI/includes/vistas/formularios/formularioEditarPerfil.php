<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

class FormularioEditarPerfil extends FormularioBase
{
    private UsuarioSA $usuarioSA;
    private int $idObjetivo;
    private bool $esGerente;
    private bool $editandoPropio;
    private object $usuario;

    private array $opcionesAvatar = [
        'avatares/default.jpg' => 'Por defecto',
        'avatares/a1.png'      => 'Avatar 1',
        'avatares/a2.png'      => 'Avatar 2',
        'avatares/a3.png'      => 'Avatar 3',
        'avatares/a4.png'      => 'Avatar 4',
    ];

    public function __construct(int $idObjetivo, bool $esGerente)
    {
        parent::__construct('formEditarPerfil', [
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
        ]);

        $this->usuarioSA = new UsuarioSA();
        $this->esGerente = $esGerente;

        $idSesion = (int)($_SESSION['usuario_id'] ?? 0);

        if (!$esGerente) {
            $idObjetivo = $idSesion;
        }

        $this->idObjetivo = $idObjetivo;
        $this->editandoPropio = ($this->idObjetivo === $idSesion);

        $usuario = $this->usuarioSA->getById($this->idObjetivo);
        if (!$usuario) {
            header('Location: ' . RUTA_APP . '/logout.php');
            exit;
        }

        $this->usuario = $usuario;
    }

    public function getTituloPagina(): string
    {
        return ($this->esGerente && !$this->editandoPropio) ? 'Editar usuario' : 'Editar perfil';
    }

    private function getUrlCancelar(): string
    {
        return ($this->esGerente && !$this->editandoPropio)
            ? (RUTA_APP . '/includes/vistas/gerente/usuarios.php')
            : (RUTA_APP . '/includes/vistas/mi_perfil.php');
    }

    private function h(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $valores = $this->getValoresFormulario($datos);
        $erroresCampos = self::generaErroresCampos(
            ['nombreUsuario', 'email', 'nombre', 'apellidos', 'password_actual', 'password_nueva', 'password_nueva_2', 'avatar_file'],
            $this->errores
        );

        $erroresGlobales = self::generaListaErroresGlobales($this->errores);

        $avatarUrl = RUTA_IMGS . '/' . ltrim((string)$valores['avatarActual'], '/');
        $btnCancelarUrl = $this->getUrlCancelar();

        ob_start();
        ?>
        <?= $erroresGlobales ?>

        <div class="form-media">
            <img
                id="avatarPreview"
                class="avatar-edit"
                src="<?= $this->h($avatarUrl) ?>"
                alt="Avatar"
            >

            <div class="stack form-panel">
                <div>
                    <label for="avatarSelect">Avatares</label>
                    <select id="avatarSelect" name="avatar_predef">
                        <?php foreach ($this->opcionesAvatar as $value => $label): ?>
                            <option value="<?= $this->h((string)$value) ?>" <?= ((string)$value === (string)$valores['avatar_predef']) ? 'selected' : '' ?>>
                                <?= $this->h((string)$label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="avatarFile">Subir</label>
                    <input id="avatarFile" type="file" name="avatar_file" accept="image/*">
                    <?= $erroresCampos['avatar_file'] ?>
                </div>

                <div>
                    <label for="nombreUsuario">Usuario</label>
                    <input
                        id="nombreUsuario"
                        type="text"
                        name="nombreUsuario"
                        value="<?= $this->h((string)$valores['nombreUsuario']) ?>"
                        required
                    >
                    <?= $erroresCampos['nombreUsuario'] ?>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?= $this->h((string)$valores['email']) ?>"
                        required
                    >
                    <?= $erroresCampos['email'] ?>
                </div>

                <div>
                    <label for="nombre">Nombre</label>
                    <input
                        id="nombre"
                        type="text"
                        name="nombre"
                        value="<?= $this->h((string)$valores['nombre']) ?>"
                        required
                    >
                    <?= $erroresCampos['nombre'] ?>
                </div>

                <div>
                    <label for="apellidos">Apellidos</label>
                    <input
                        id="apellidos"
                        type="text"
                        name="apellidos"
                        value="<?= $this->h((string)$valores['apellidos']) ?>"
                        required
                    >
                    <?= $erroresCampos['apellidos'] ?>
                </div>

                <hr>

                <?php if ($this->editandoPropio): ?>
                    <div>
                        <label for="password_actual">Contraseña actual</label>
                        <input
                            id="password_actual"
                            type="password"
                            name="password_actual"
                            autocomplete="current-password"
                        >
                        <?= $erroresCampos['password_actual'] ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="password_nueva">Nueva contraseña</label>
                    <input
                        id="password_nueva"
                        type="password"
                        name="password_nueva"
                        autocomplete="new-password"
                    >
                    <?= $erroresCampos['password_nueva'] ?>
                </div>

                <div>
                    <label for="password_nueva_2">Repetir nueva contraseña</label>
                    <input
                        id="password_nueva_2"
                        type="password"
                        name="password_nueva_2"
                        autocomplete="new-password"
                    >
                    <?= $erroresCampos['password_nueva_2'] ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
            <a class="btn btn-light" href="<?= $this->h($btnCancelarUrl) ?>">Cancelar</a>
        </div>

        <script>
            (function () {
                const IMG_BASE = "<?= $this->h(RUTA_IMGS) ?>";
                const img = document.getElementById("avatarPreview");
                const sel = document.getElementById("avatarSelect");
                const file = document.getElementById("avatarFile");

                if (sel) {
                    sel.addEventListener("change", function () {
                        img.src = IMG_BASE + "/" + sel.value;
                    });
                }

                if (file) {
                    file.addEventListener("change", function () {
                        const f = file.files[0];
                        if (f) {
                            img.src = URL.createObjectURL(f);
                        }
                    });
                }
            })();
        </script>
        <?php

        return ob_get_clean();
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nombreUsuario = trim((string) filter_input(INPUT_POST, 'nombreUsuario', FILTER_SANITIZE_SPECIAL_CHARS));
        $email         = trim((string) filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
        $nombre        = trim((string) filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS));
        $apellidos     = trim((string) filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_SPECIAL_CHARS));
        $avatarPredef = (string) filter_input(INPUT_POST, 'avatar_predef', FILTER_SANITIZE_SPECIAL_CHARS) ?: $this->getAvatarActual();

        $passwordActual = (string) filter_input(INPUT_POST, 'password_actual', FILTER_SANITIZE_SPECIAL_CHARS);
        $passwordNueva  = (string) filter_input(INPUT_POST, 'password_nueva', FILTER_SANITIZE_SPECIAL_CHARS);
        $passwordNueva2 = (string) filter_input(INPUT_POST, 'password_nueva_2', FILTER_SANITIZE_SPECIAL_CHARS);

        if (mb_strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'Usuario mínimo 4 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores['email'] = 'Email no válido.';
        }

        if ($nombre === '') {
            $this->errores['nombre'] = 'Nombre obligatorio.';
        }

        if ($apellidos === '') {
            $this->errores['apellidos'] = 'Apellidos obligatorios.';
        }

        $avatarFinal = $avatarPredef;

        if (!empty($_FILES['avatar_file']) && is_uploaded_file($_FILES['avatar_file']['tmp_name'])) {
            $f = $_FILES['avatar_file'];

            if (($f['size'] ?? 0) > 2 * 1024 * 1024) {
                $this->errores['avatar_file'] = 'La imagen supera 2MB.';
            } else {
                $mime = mime_content_type($f['tmp_name']);
                $permitidos = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];

                if (!in_array($mime, $permitidos, true)) {
                    $this->errores['avatar_file'] = 'Formato no permitido (PNG/JPG/WEBP/GIF).';
                }
            }

            if (empty($this->errores['avatar_file'])) {
                $ext = strtolower(pathinfo((string)($f['name'] ?? ''), PATHINFO_EXTENSION) ?: 'png');
                $nombreArchivo = 'avatares/u_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $destinoAbs = RAIZ_APP . '/img/' . $nombreArchivo;

                if (!move_uploaded_file($f['tmp_name'], $destinoAbs)) {
                    $this->errores['avatar_file'] = 'No se pudo guardar la imagen.';
                } else {
                    $avatarFinal = $nombreArchivo;
                }
            }
        }

        $cambiarPassword = ($passwordActual !== '' || $passwordNueva !== '' || $passwordNueva2 !== '');

        if ($cambiarPassword) {
            if ($this->editandoPropio) {
                if ($passwordActual === '') {
                    $this->errores['password_actual'] = 'Debes introducir tu contraseña actual.';
                } elseif (!$this->usuarioSA->verificarPassword((int)$this->usuario->getId(), $passwordActual)) {
                    $this->errores['password_actual'] = 'La contraseña actual no es correcta.';
                }
            }

            if ($passwordNueva === '') {
                $this->errores['password_nueva'] = 'La nueva contraseña no puede estar vacía.';
            } elseif (mb_strlen($passwordNueva) < 4) {
                $this->errores['password_nueva'] = 'La nueva contraseña debe tener al menos 4 caracteres.';
            }

            if ($passwordNueva2 === '') {
                $this->errores['password_nueva_2'] = 'Debes repetir la nueva contraseña.';
            } elseif ($passwordNueva !== $passwordNueva2) {
                $this->errores['password_nueva_2'] = 'La confirmación de la nueva contraseña no coincide.';
            }
        }

        if (!empty($this->errores)) {
            return;
        }

        $nuevo = $this->usuarioSA->actualizarPerfil(
            (int)$this->usuario->getId(),
            $nombreUsuario,
            $email,
            $nombre,
            $apellidos,
            $avatarFinal,
            $cambiarPassword ? $passwordNueva : null
        );

        if (!$nuevo) {
            $this->errores[] = 'No se pudo actualizar (usuario o email ya existente).';
            return;
        }

        if ($this->editandoPropio) {
            $_SESSION['nombre_usuario'] = $nuevo->getNombreUsuario();
            $_SESSION['nombre'] = $nuevo->getNombre();
            $_SESSION['avatar'] = $nuevo->getAvatar();
        }

        $this->urlRedireccion = ($this->esGerente && !$this->editandoPropio)
            ? (RUTA_VISTAS . '/gerente/usuarios.php')
            : (RUTA_VISTAS . '/mi_perfil.php');
    }

    private function getAvatarActual(): string
    {
        $avatar = $this->usuario->getAvatar();
        return $avatar ?: 'avatares/default.jpg';
    }

    private function getValoresFormulario(array $datos): array
    {
        return [
            'nombreUsuario' => $datos['nombreUsuario'] ?? (string)$this->usuario->getNombreUsuario(),
            'email'         => $datos['email'] ?? (string)$this->usuario->getEmail(),
            'nombre'        => $datos['nombre'] ?? (string)$this->usuario->getNombre(),
            'apellidos'     => $datos['apellidos'] ?? (string)$this->usuario->getApellidos(),
            'avatar_predef' => $datos['avatar_predef'] ?? $this->getAvatarActual(),
            'avatarActual'  => $datos['avatar_predef'] ?? $this->getAvatarActual(),
        ];
    }
}