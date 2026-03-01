<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

class FormularioLogin extends FormularioBase
{
    public function __construct(){
        parent::__construct('formLogin', ['urlRedireccion' => RUTA_APP . '/index.php']);
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $nombreUsuario = $datos['nombreUsuario'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombreUsuario', 'password'], $this->errores);

        $nombreUsuarioEsc = htmlspecialchars($nombreUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
        {$htmlErroresGlobales}
        <fieldset>
            <legend>Acceso</legend>

            <div>
                <label for="nombreUsuario">Nombre de usuario</label><br>
                <input id="nombreUsuario" type="text" name="nombreUsuario" value="{$nombreUsuarioEsc}" required>
                {$erroresCampos['nombreUsuario']}
            </div>

            <div style="margin-top: 10px;">
                <label for="password">Contraseña</label><br>
                <input id="password" type="password" name="password" required>
                {$erroresCampos['password']}
            </div>

            <div style="margin-top: 10px;">
                <button type="submit" name="login">Entrar</button>
            </div>
        </fieldset>
        HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $this->errores = [];

        $nombreUsuario = filter_var(trim($datos['nombreUsuario'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = trim($datos['password'] ?? '');

        if (!$nombreUsuario || mb_strlen($nombreUsuario) < 4) {
            $this->errores['nombreUsuario'] = 'El nombre de usuario debe tener al menos 4 caracteres.';
        }
        if (!$password || mb_strlen($password) < 4) {
            $this->errores['password'] = 'La contraseña debe tener al menos 4 caracteres.';
        }
        if ($this->errores) return;

        try {
            $sa = new UsuarioSA();
            $usuario = $sa->login($nombreUsuario, $password);

            if (!$usuario) {
                $this->errores[] = 'Usuario o contraseña incorrectos.';
                return;
            }

            $_SESSION['login'] = true;
            $_SESSION['usuario_id'] = $usuario->getId();
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            $_SESSION['nombre'] = $usuario->getNombre();
            $_SESSION['roles'] = array_map(fn($r) => $r->getNombre(), $usuario->getRoles());

            $_SESSION['esGerente']  = $sa->tieneRol($usuario, 'gerente');
            $_SESSION['esCamarero'] = $sa->tieneRol($usuario, 'camarero');
            $_SESSION['esCocinero'] = $sa->tieneRol($usuario, 'cocinero');

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->errores[] = 'Error interno al iniciar sesión.';
        }
    }
}