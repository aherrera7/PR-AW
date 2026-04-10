<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';
require_once RAIZ_APP . '/includes/app/dto/CategoriaDTO.php';

class FormularioCategoria extends FormularioBase
{
    private ?int $idCategoria;
    private ?CategoriaDTO $categoria;
    private string $urlCancelar;
    private string $urlRedireccionFinal;

    public function __construct(?int $idCategoria = null)
    {
        parent::__construct('formCategoria', [
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
        ]);

        $this->idCategoria = $idCategoria;
        $this->categoria = null;
        $this->urlCancelar = RUTA_APP . '/includes/vistas/gerente/categorias_listar.php';
        $this->urlRedireccionFinal = RUTA_APP . '/includes/vistas/gerente/categorias_listar.php';

        if ($this->idCategoria !== null) {
            $categoria = CategoriaSA::obtener($this->idCategoria);

            if ($categoria === null) {
                header('Location: ' . $this->urlCancelar);
                exit;
            }

            $this->categoria = $categoria;
        }
    }

    private function h(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function estaEditando(): bool
    {
        return $this->categoria !== null;
    }

    private function getTituloBoton(): string
    {
        return $this->estaEditando() ? 'Guardar' : 'Crear';
    }

    private function getTextoCabecera(): string
    {
        return $this->estaEditando() ? 'Editar categoría' : 'Nueva categoría';
    }

    private function getValoresFormulario(array $datos): array
    {
        $nombre = '';
        $descripcion = '';
        $imagenActual = '';

        if ($this->categoria !== null) {
            $nombre = $this->categoria->getNombre();
            $descripcion = $this->categoria->getDescripcion() ?? '';
            $imagenActual = $this->categoria->getImagen() ?? '';
        }

        return [
            'nombre' => $datos['nombre'] ?? $nombre,
            'descripcion' => $datos['descripcion'] ?? $descripcion,
            'imagenActual' => $imagenActual,
        ];
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $valores = $this->getValoresFormulario($datos);
        $erroresCampos = self::generaErroresCampos(
            ['nombre', 'descripcion', 'imagen'],
            $this->errores
        );
        $erroresGlobales = self::generaListaErroresGlobales($this->errores);

        $imagenActual = $valores['imagenActual'];
        $mostrarImagen = $imagenActual !== '';
        $urlImagen = $mostrarImagen ? RUTA_IMGS . '/' . ltrim($imagenActual, '/') : '';

        ob_start();
        ?>
        <div class="stack">
            <?= $erroresGlobales ?>

            <div class="header-bar">
                <h1><?= $this->h($this->getTextoCabecera()) ?></h1>
                <a class="btn btn-light" href="<?= $this->h($this->urlCancelar) ?>">Volver</a>
            </div>

            <?php if ($mostrarImagen): ?>
                <div>
                    <label>Imagen actual</label>
                    <img
                        class="image-box"
                        src="<?= $this->h($urlImagen) ?>"
                        alt="Imagen actual de la categoría"
                    >
                </div>
            <?php endif; ?>

            <div>
                <label for="nombre">Nombre</label>
                <input
                    id="nombre"
                    name="nombre"
                    type="text"
                    required
                    value="<?= $this->h($valores['nombre']) ?>"
                >
                <?= $erroresCampos['nombre'] ?>
            </div>

            <div>
                <label for="descripcion">Descripción (opcional)</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                ><?= $this->h($valores['descripcion']) ?></textarea>
                <?= $erroresCampos['descripcion'] ?>
            </div>

            <div>
                <label for="imagen">
                    <?= $this->estaEditando() ? 'Cambiar imagen (opcional)' : 'Imagen (opcional)' ?>
                </label>
                <input
                    id="imagen"
                    name="imagen"
                    type="file"
                    accept="image/*"
                >
                <?= $erroresCampos['imagen'] ?>
                <div class="muted">
                    <?= $this->estaEditando()
                        ? 'Si subes una nueva imagen, se reemplaza la anterior.'
                        : 'Se guardará en /img/categorias/' ?>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit"><?= $this->h($this->getTituloBoton()) ?></button>
                <a class="btn btn-light" href="<?= $this->h($this->urlCancelar) ?>">Cancelar</a>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nombre = trim($datos['nombre'] ?? '');
        $descripcionTexto = trim($datos['descripcion'] ?? '');
        $descripcion = $descripcionTexto === '' ? null : $descripcionTexto;

        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre es obligatorio.';
        }

        if (mb_strlen($nombre) > 100) {
            $this->errores['nombre'] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($descripcion !== null && mb_strlen($descripcion) > 1000) {
            $this->errores['descripcion'] = 'La descripción no puede superar 1000 caracteres.';
        }

        if (!empty($this->errores)) {
            return;
        }

        try {
            if ($this->estaEditando()) {
                $categoria = new CategoriaDTO(
                    $this->idCategoria,
                    $nombre,
                    $descripcion,
                    $this->categoria?->getImagen()
                );

                CategoriaSA::actualizarConUpload($categoria, $_FILES['imagen'] ?? null);
            } else {
                $categoria = new CategoriaDTO(
                    null,
                    $nombre,
                    $descripcion,
                    null
                );

                CategoriaSA::crearConUpload($categoria, $_FILES['imagen'] ?? null);
            }

            $this->urlRedireccion = $this->urlRedireccionFinal;
        } catch (Throwable $e) {
            $this->errores[] = $e->getMessage();
        }
    }
}