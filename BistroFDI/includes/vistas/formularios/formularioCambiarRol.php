<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/UsuarioSA.php';

class FormularioCambiarRol extends FormularioBase
{
    private UsuarioSA $usuarioSA;
    private int $idUsuario;
    private UsuarioDTO $usuario;
    private array $rolesDisponibles = ['cliente', 'camarero', 'cocinero', 'gerente'];

    public function __construct(int $idUsuario)
    {
        parent::__construct('formCambiarRol_' . $idUsuario, [
            'method' => 'POST',
            'urlRedireccion' => RUTA_VISTAS . '/gerente/usuarios.php',
        ]);

        $this->usuarioSA = new UsuarioSA();
        $this->idUsuario = $idUsuario;

        $usuario = $this->usuarioSA->getById($this->idUsuario);
        if (!$usuario) {
            header('Location: ' . RUTA_VISTAS . '/gerente/usuarios.php');
            exit;
        }

        $this->usuario = $usuario;
    }

    private function h(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function getRolActual(): string
    {
        $roles = $this->usuario->getRoles();
        return (count($roles) > 0) ? (string)$roles[0]->getNombre() : 'cliente';
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $rolSeleccionado = (string)($datos['rol'] ?? $this->getRolActual());

        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['rol'], $this->errores);

        $urlVolver = RUTA_VISTAS . '/gerente/usuarios.php';
        $nombreUsuario = (string)$this->usuario->getNombreUsuario();

        ob_start();
        ?>
        <?= $erroresGlobales ?>

        <div class="stack">
            <p>Usuario: <strong>@<?= $this->h($nombreUsuario) ?></strong></p>

            <div>
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <?php foreach ($this->rolesDisponibles as $rol): ?>
                        <option
                            value="<?= $this->h($rol) ?>"
                            <?= ($rol === $rolSeleccionado) ? 'selected' : '' ?>
                        >
                            <?= $this->h(ucfirst($rol)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= $erroresCampos['rol'] ?>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">Guardar</button>
                <a class="btn btn-light" href="<?= $this->h($urlVolver) ?>">Cancelar</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nuevoRol = trim((string)($datos['rol'] ?? ''));

        if (!in_array($nuevoRol, $this->rolesDisponibles, true)) {
            $this->errores['rol'] = 'Rol no válido.';
            return;
        }

        if (
            !empty($_SESSION['usuario_id']) &&
            (int)$_SESSION['usuario_id'] === (int)$this->usuario->getId() &&
            $nuevoRol !== 'gerente'
        ) {
            $this->errores['rol'] = 'No puedes quitarte el rol gerente a ti mismo.';
            return;
        }

        $ok = $this->usuarioSA->cambiarRolUsuario((int)$this->usuario->getId(), $nuevoRol);

        if (!$ok) {
            $this->errores[] = 'No se pudo cambiar el rol.';
        }
    }
}