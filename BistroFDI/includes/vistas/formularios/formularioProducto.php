<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';
require_once RAIZ_APP . '/includes/app/sa/CategoriaSA.php';
require_once RAIZ_APP . '/includes/app/dto/ProductoDTO.php';

class FormularioProducto extends FormularioBase
{
    private ?ProductoDTO $producto;
    private ?int $idProducto;
    private array $categorias;
    private string $urlVolver;
    private string $urlRedireccionFinal;

    public function __construct(?int $idProducto = null)
    {
        parent::__construct('formProducto', [
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
        ]);

        $this->idProducto = $idProducto;
        $this->producto = null;
        $this->categorias = CategoriaSA::listar();

        // --- LÓGICA DE RETORNO DINÁMICO ---
        // Capturamos la categoría de la URL para saber a dónde volver
        $idCatUrl = filter_input(INPUT_GET, 'id_cat', FILTER_VALIDATE_INT);
        
        if ($idCatUrl) {
            // Si venimos de una categoría específica en la carta
            $this->urlVolver = "productos_carta.php?id_cat=$idCatUrl";
            $this->urlRedireccionFinal = "productos_carta.php?id_cat=$idCatUrl";
        } else {
            // Por defecto si no hay categoría (toda la carta)
            $this->urlVolver = "productos_carta.php";
            $this->urlRedireccionFinal = "productos_carta.php";
        }
        // ----------------------------------

        if ($this->idProducto !== null) {
            $producto = ProductoSA::obtener($this->idProducto);

            if ($producto === null) {
                header('Location: ' . $this->urlVolver);
                exit;
            }

            $this->producto = $producto;
        }
    }

    private function h(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function estaEditando(): bool
    {
        return $this->producto !== null;
    }

    private function getTitulo(): string
    {
        return $this->estaEditando() ? 'Editar Producto' : 'Nuevo Producto';
    }

    private function getTextoBoton(): string
    {
        return $this->estaEditando() ? 'Guardar cambios' : 'Crear Producto';
    }

    private function getValoresFormulario(array $datos): array
    {
        $nombre = '';
        $idCategoria = '';
        $descripcion = '';
        $precioBase = '0.00';
        $iva = '10';
        $disponible = true;
        $esCocina = true;
        $imagenesActuales = [];

        if ($this->producto !== null) {
            $nombre = $this->producto->getNombre();
            $idCategoria = (string) $this->producto->getIdCategoria();
            $descripcion = $this->producto->getDescripcion() ?? '';
            $precioBase = number_format($this->producto->getPrecioBase(), 2, '.', '');
            $iva = (string) $this->producto->getIva();
            $disponible = $this->producto->isDisponible();
            $esCocina = $this->producto->getEsCocina();
            $imagenesActuales = $this->producto->getImagenes();
        }

        $disponibleEnviado = $datos['disponible'] ?? null;
        $esCocinaEnviado = $datos['es_cocina'] ?? null;
        $borrarFotos = $datos['borrar_fotos'] ?? [];

        return [
            'nombre' => $datos['nombre'] ?? $nombre,
            'id_categoria' => $datos['id_categoria'] ?? $idCategoria,
            'descripcion' => $datos['descripcion'] ?? $descripcion,
            'precio_base' => $datos['precio_base'] ?? $precioBase,
            'iva' => $datos['iva'] ?? $iva,
            'disponible' => $disponibleEnviado !== null ? true : $disponible,
            'es_cocina' => $esCocinaEnviado !== null ? true : $esCocina,
            'imagenesActuales' => $imagenesActuales,
            'borrar_fotos' => is_array($borrarFotos) ? $borrarFotos : [],
        ];
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $valores = $this->getValoresFormulario($datos);
        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(
            ['nombre', 'id_categoria', 'descripcion', 'precio_base', 'iva', 'imagenes'],
            $this->errores
        );

        $precioBaseJs = json_encode((string) $valores['precio_base'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $ivaJs = json_encode((string) $valores['iva'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        ob_start();
        ?>
        <div class="stack">
            <?= $erroresGlobales ?>

            <div class="header-bar">
                <h1><?= $this->h($this->getTitulo()) ?></h1>
                <a class="btn btn-light" href="<?= $this->h($this->urlVolver) ?>">Volver</a>
            </div>

            <?php if ($this->estaEditando() && !empty($valores['imagenesActuales'])): ?>
                <div>
                    <label>Imágenes actuales (marca para eliminar)</label>
                    <div class="thumb-list">
                        <?php foreach ($valores['imagenesActuales'] as $img): ?>
                            <?php
                            $rutaImg = is_string($img) ? $img : '';
                            if ($rutaImg === '') {
                                continue;
                            }
                            $checked = in_array($rutaImg, $valores['borrar_fotos'], true);
                            ?>
                            <div class="thumb-card">
                                <img
                                    class="thumb-img"
                                    src="<?= $this->h(RUTA_IMGS . '/' . ltrim($rutaImg, '/')) ?>"
                                    alt="Imagen del producto"
                                >
                                <label>
                                    <input
                                        type="checkbox"
                                        name="borrar_fotos[]"
                                        value="<?= $this->h($rutaImg) ?>"
                                        <?= $checked ? 'checked' : '' ?>
                                    >
                                    <small>Borrar</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label for="nombre">Nombre del Producto</label>
                    <input
                        id="nombre"
                        name="nombre"
                        type="text"
                        required
                        value="<?= $this->h((string) $valores['nombre']) ?>"
                    >
                    <?= $erroresCampos['nombre'] ?>
                </div>

                <div>
                    <label for="id_categoria">Categoría</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="" disabled <?= $valores['id_categoria'] === '' ? 'selected' : '' ?>>
                            Selecciona una categoría
                        </option>
                        <?php foreach ($this->categorias as $cat): ?>
                            <?php
                            $idCat = $cat->getId();
                            $idCatTexto = $idCat !== null ? (string) $idCat : '';
                            ?>
                            <option
                                value="<?= $this->h($idCatTexto) ?>"
                                <?= $idCatTexto === (string) $valores['id_categoria'] ? 'selected' : '' ?>
                            >
                                <?= $this->h($cat->getNombre()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= $erroresCampos['id_categoria'] ?>
                </div>
            </div>

            <div>
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"><?= $this->h((string) $valores['descripcion']) ?></textarea>
                <?= $erroresCampos['descripcion'] ?>
            </div>

            <div class="form-grid-3">
                <div>
                    <label for="precio_base">Precio Base (€)</label>
                    <input
                        id="precio_base"
                        name="precio_base"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        value="<?= $this->h((string) $valores['precio_base']) ?>"
                    >
                    <?= $erroresCampos['precio_base'] ?>
                </div>

                <div>
                    <label for="iva">IVA (%)</label>
                    <select id="iva" name="iva">
                        <option value="4" <?= (string) $valores['iva'] === '4' ? 'selected' : '' ?>>4% (Superreducido)</option>
                        <option value="10" <?= (string) $valores['iva'] === '10' ? 'selected' : '' ?>>10% (Reducido)</option>
                        <option value="21" <?= (string) $valores['iva'] === '21' ? 'selected' : '' ?>>21% (General)</option>
                    </select>
                    <?= $erroresCampos['iva'] ?>
                </div>

                <div class="price-box">
                    <small class="muted">PVP Final calculado:</small><br>
                    <strong id="precio_final" class="price-value">0,00€</strong>
                </div>
            </div>

            <div>
                <label for="imagenes">
                    <?= $this->estaEditando() ? 'Añadir nuevas imágenes' : 'Imágenes del producto' ?>
                </label>
                <input
                    id="imagenes"
                    name="imagenes[]"
                    type="file"
                    accept="image/*"
                    multiple
                    <?= $this->estaEditando() ? '' : 'required' ?>
                >
                <?= $erroresCampos['imagenes'] ?>
                <div class="muted">
                    <?= $this->estaEditando()
                        ? 'Puedes subir varios archivos a la vez.'
                        : 'Selecciona una o varias fotos para la galería del producto.' ?>
                </div>
            </div>

            <div class="check-row">
                <input
                    id="disponible"
                    name="disponible"
                    type="checkbox"
                    <?= $valores['disponible'] ? 'checked' : '' ?>
                >
                <label for="disponible">Disponible para la venta inmediatamente</label>
            </div>
            <div class="check-row">
                <input
                    id="es_cocina"
                    name="es_cocina"
                    type="checkbox"
                    <?= $valores['es_cocina'] ? 'checked' : '' ?>
                >
                <label for="es_cocina">¿Es un producto de cocina? (Se enviará al panel del cocinero)</label>
            </div>
            <div class="form-actions">
                <button class="btn" type="submit"><?= $this->h($this->getTextoBoton()) ?></button>
                <a class="btn btn-light" href="<?= $this->h($this->urlVolver) ?>">Cancelar</a>
            </div>
        </div>

        <script>
            (function () {
                const pb = document.getElementById('precio_base');
                const iv = document.getElementById('iva');
                const pf = document.getElementById('precio_final');

                function actualizarPVP() {
                    const base = parseFloat(pb.value || <?= $precioBaseJs ?>) || 0;
                    const iva = parseInt(iv.value || <?= $ivaJs ?>, 10) || 0;
                    const total = base * (1 + (iva / 100));
                    pf.textContent = total.toLocaleString('es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '€';
                }

                if (pb) {
                    pb.addEventListener('input', actualizarPVP);
                }

                if (iv) {
                    iv.addEventListener('change', actualizarPVP);
                }

                actualizarPVP();
            })();
        </script>
        <?php

        return ob_get_clean();
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $idCategoriaTexto = trim((string) ($datos['id_categoria'] ?? ''));
        $descripcionTexto = trim((string) ($datos['descripcion'] ?? ''));
        $precioBaseTexto = trim((string) ($datos['precio_base'] ?? ''));
        $ivaTexto = trim((string) ($datos['iva'] ?? ''));
        $disponible = array_key_exists('disponible', $datos);
        $esCocina = array_key_exists('es_cocina', $datos);
        $ofertado = true;
        $descripcion = $descripcionTexto === '' ? null : $descripcionTexto;

        $idCategoria = filter_var($idCategoriaTexto, FILTER_VALIDATE_INT);
        if ($idCategoria === false || $idCategoria <= 0) {
            $this->errores['id_categoria'] = 'Debes seleccionar una categoría válida.';
        }

        $precioBase = filter_var($precioBaseTexto, FILTER_VALIDATE_FLOAT);
        if ($precioBase === false || $precioBase < 0) {
            $this->errores['precio_base'] = 'El precio base debe ser un número válido mayor o igual que 0.';
        }

        $iva = filter_var($ivaTexto, FILTER_VALIDATE_INT);
        if ($iva === false || !in_array($iva, [4, 10, 21], true)) {
            $this->errores['iva'] = 'Debes seleccionar un IVA válido.';
        }

        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre es obligatorio.';
        }

        if (mb_strlen($nombre) > 120) {
            $this->errores['nombre'] = 'El nombre no puede superar 120 caracteres.';
        }

        if ($descripcion !== null && mb_strlen($descripcion) > 2000) {
            $this->errores['descripcion'] = 'La descripción no puede superar 2000 caracteres.';
        }

        $imagenesSubidas = $_FILES['imagenes'] ?? null;
        $hayImagenesNuevas = false;

        if (is_array($imagenesSubidas) && isset($imagenesSubidas['error']) && is_array($imagenesSubidas['error'])) {
            foreach ($imagenesSubidas['error'] as $errorImg) {
                if ($errorImg !== UPLOAD_ERR_NO_FILE) {
                    $hayImagenesNuevas = true;
                    break;
                }
            }
        }

        if (!$this->estaEditando() && !$hayImagenesNuevas) {
            $this->errores['imagenes'] = 'Debes subir al menos una imagen.';
        }

        $borrarFotos = $datos['borrar_fotos'] ?? [];
        if (!is_array($borrarFotos)) {
            $borrarFotos = [];
        }

        if (!empty($this->errores)) {
            return;
        }

        $idProducto = $this->producto?->getId();

        try {
            if ($this->estaEditando()) {
                $producto = new ProductoDTO(
                    $idProducto,
                    $idCategoria,
                    $nombre,
                    $descripcion,
                    $precioBase,
                    $iva,
                    $disponible,
                    $this->producto?->isOfertado() ?? true,
                    [],
                    $esCocina
                );

                $ok = ProductoSA::actualizarConUpload($producto, $imagenesSubidas);

                if (!$ok) {
                    $this->errores[] = 'No se pudo actualizar el producto.';
                    return;
                }

                if (!empty($borrarFotos) && $idProducto !== null) {
                    ProductoSA::borrarImagenes($idProducto, $borrarFotos);
                }
            } else {
                $producto = new ProductoDTO(
                    null,
                    $idCategoria,
                    $nombre,
                    $descripcion,
                    $precioBase,
                    $iva,
                    $disponible,
                    $ofertado,
                    [],
                    $esCocina
                );

                ProductoSA::crearConUpload($producto, $imagenesSubidas);
            }

            // Al terminar con éxito, usamos la redirección final configurada en el constructor
            $this->urlRedireccion = $this->urlRedireccionFinal;
            
        } catch (Throwable $e) {
            $this->errores[] = $e->getMessage();
        }
    }
}