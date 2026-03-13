<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

if (empty($_SESSION['login']) || empty($_SESSION['usuario_id'])) {
    header('Location: ' . RUTA_APP . '/includes/vistas/login.php');
    exit;
}

$sa = new UsuarioSA();

if (!function_exists('h')) {
    function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$esGerente = !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;

$idObjetivo = (int)($_SESSION['usuario_id']);
if ($esGerente && isset($_GET['id'])) {
    $idObjetivo = (int)$_GET['id'];
}
if (!$esGerente && isset($_GET['id'])) {
    $idObjetivo = (int)$_SESSION['usuario_id'];
}

$editandoPropio = ($idObjetivo === (int)$_SESSION['usuario_id']);

$usuario = $sa->getById($idObjetivo);
if (!$usuario) {
    header('Location: ' . RUTA_APP . '/logout.php');
    exit;
}

$errores = [];

$opciones = [
    'avatares/default.jpg' => 'Por defecto',
    'avatares/a1.png'      => 'Avatar 1',
    'avatares/a2.png'      => 'Avatar 2',
    'avatares/a3.png'      => 'Avatar 3',
    'avatares/a4.png'      => 'Avatar 4',
];

/* Valores iniciales del formulario */
$nombreUsuario = (string)$usuario->getNombreUsuario();
$email         = (string)$usuario->getEmail();
$nombre        = (string)$usuario->getNombre();
$apellidos     = (string)$usuario->getApellidos();
$avatarActual  = $usuario->getAvatar() ?: 'avatares/default.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = trim((string)($_POST['nombreUsuario'] ?? ''));
    $email         = trim((string)($_POST['email'] ?? ''));
    $nombre        = trim((string)($_POST['nombre'] ?? ''));
    $apellidos     = trim((string)($_POST['apellidos'] ?? ''));
    $avatarPredef  = (string)($_POST['avatar_predef'] ?? $avatarActual);

    $passwordActual = (string)($_POST['password_actual'] ?? '');
    $passwordNueva  = (string)($_POST['password_nueva'] ?? '');
    $passwordNueva2 = (string)($_POST['password_nueva_2'] ?? '');

    if (mb_strlen($nombreUsuario) < 4) {
        $errores[] = 'Usuario mínimo 4 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email no válido.';
    }

    if ($nombre === '') {
        $errores[] = 'Nombre obligatorio.';
    }

    if ($apellidos === '') {
        $errores[] = 'Apellidos obligatorios.';
    }

    $avatarFinal = $avatarPredef;

    if (!empty($_FILES['avatar_file']) && is_uploaded_file($_FILES['avatar_file']['tmp_name'])) {
        $f = $_FILES['avatar_file'];

        if (($f['size'] ?? 0) > 2 * 1024 * 1024) {
            $errores[] = 'La imagen supera 2MB.';
        }

        $mime = mime_content_type($f['tmp_name']);
        $permitidos = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];

        if (!in_array($mime, $permitidos, true)) {
            $errores[] = 'Formato no permitido (PNG/JPG/WEBP/GIF).';
        }

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

    $cambiarPassword = ($passwordActual !== '' || $passwordNueva !== '' || $passwordNueva2 !== '');

    if ($cambiarPassword) {
    if ($editandoPropio) {
        if ($passwordActual === '') {
            $errores[] = 'Debes introducir tu contraseña actual.';
        } elseif (!$sa->verificarPassword((int)$usuario->getId(), $passwordActual)) {
            $errores[] = 'La contraseña actual no es correcta.';
        }
    }

    if ($passwordNueva === '') {
        $errores[] = 'La nueva contraseña no puede estar vacía.';
    } elseif (mb_strlen($passwordNueva) < 4) {
        $errores[] = 'La nueva contraseña debe tener al menos 4 caracteres.';
    }

    if ($passwordNueva2 === '') {
        $errores[] = 'Debes repetir la nueva contraseña.';
    } elseif ($passwordNueva !== $passwordNueva2) {
        $errores[] = 'La confirmación de la nueva contraseña no coincide.';
    }
}

    if (!$errores) {
        $nuevo = $sa->actualizarPerfil(
            (int)$usuario->getId(),
            $nombreUsuario,
            $email,
            $nombre,
            $apellidos,
            $avatarFinal,
            $cambiarPassword ? $passwordNueva : null
        );

        if (!$nuevo) {
            $errores[] = 'No se pudo actualizar (usuario o email ya existente).';
        } else {
            if ($editandoPropio) {
                $_SESSION['nombre_usuario'] = $nuevo->getNombreUsuario();
                $_SESSION['nombre'] = $nuevo->getNombre();
                $_SESSION['avatar'] = $nuevo->getAvatar();
            }

            if ($esGerente && !$editandoPropio) {
                header('Location: ' . RUTA_APP . '/includes/vistas/gerente/usuarios.php');
            } else {
                header('Location: ' . RUTA_APP . '/includes/vistas/mi_perfil.php');
            }
            exit;
        }
    }

    $avatarActual = $avatarFinal;
}

$avatarUrl = RUTA_IMGS . '/' . ltrim($avatarActual, '/');

$tituloPagina = ($esGerente && !$editandoPropio) ? 'Editar usuario' : 'Editar perfil';
$btnCancelarUrl = ($esGerente && !$editandoPropio)
    ? (RUTA_APP . '/includes/vistas/gerente/usuarios.php')
    : (RUTA_APP . '/includes/vistas/mi_perfil.php');

$erroresHtml = '';
if (!empty($errores)) {
    $erroresHtml .= '<ul class="errores">';
    foreach ($errores as $e) {
        $erroresHtml .= '<li>' . h((string)$e) . '</li>';
    }
    $erroresHtml .= '</ul>';
}

ob_start();
?>

<section id="contenido">
    <h2><?= h($tituloPagina) ?></h2>
    <?= $erroresHtml ?>

    <div class="card">
        <form method="post" enctype="multipart/form-data" class="stack">

            <div class="form-media">
                <img
                    id="avatarPreview"
                    class="avatar-edit"
                    src="<?= h($avatarUrl) ?>"
                    alt="Avatar"
                >

                <div class="stack form-panel">
                    <div>
                        <label for="avatarSelect">Avatares</label>
                        <select id="avatarSelect" name="avatar_predef">
                            <?php foreach ($opciones as $value => $label): ?>
                                <option value="<?= h((string)$value) ?>" <?= ((string)$value === (string)$avatarActual) ? 'selected' : '' ?>>
                                    <?= h((string)$label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="avatarFile">Subir</label>
                        <input id="avatarFile" type="file" name="avatar_file" accept="image/*">
                    </div>

                    <div>
                        <label for="nombreUsuario">Usuario</label>
                        <input
                            id="nombreUsuario"
                            type="text"
                            name="nombreUsuario"
                            value="<?= h($nombreUsuario) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="<?= h($email) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="nombre">Nombre</label>
                        <input
                            id="nombre"
                            type="text"
                            name="nombre"
                            value="<?= h($nombre) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="apellidos">Apellidos</label>
                        <input
                            id="apellidos"
                            type="text"
                            name="apellidos"
                            value="<?= h($apellidos) ?>"
                            required
                        >
                    </div>

                    <hr>

                    <?php if ($editandoPropio): ?>
                        <div>
                            <label for="password_actual">Contraseña actual</label>
                            <input
                                id="password_actual"
                                type="password"
                                name="password_actual"
                                autocomplete="current-password"
                            >
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
                    </div>

                    <div>
                        <label for="password_nueva_2">Repetir nueva contraseña</label>
                        <input
                            id="password_nueva_2"
                            type="password"
                            name="password_nueva_2"
                            autocomplete="new-password"
                        >
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
                <a class="btn btn-light" href="<?= h($btnCancelarUrl) ?>">Cancelar</a>
            </div>

            <script>
                (function () {
                    const IMG_BASE = "<?= h(RUTA_IMGS) ?>";
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

        </form>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';