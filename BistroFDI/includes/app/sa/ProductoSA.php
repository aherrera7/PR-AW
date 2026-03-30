<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/ProductoDAO.php';
require_once RAIZ_APP . '/includes/app/dto/ProductoDTO.php';

class ProductoSA
{
    private const IVAS_VALIDOS = [4, 10, 21];
    private const DIR_REL_IMGS = '/img/productos';
    private const DIR_BD_IMGS  = 'productos';
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    private static function dao(): ProductoDAO
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new ProductoDAO($conn);
    }

    /** @return ProductoDTO[] */
    public static function listar(?int $idCategoria = null, bool $soloOfertados = false): array
    {
        return self::dao()->findAll($idCategoria, $soloOfertados);
    }

    public static function obtener(int $id): ?ProductoDTO
    {
        return self::dao()->findById($id);
    }

    private static function validarProducto(ProductoDTO $producto): void
    {
        if ($producto->getIdCategoria() <= 0) {
            throw new InvalidArgumentException('Categoría inválida.');
        }

        $nombre = trim($producto->getNombre());
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del producto no puede estar vacío.');
        }

        if ($producto->getPrecioBase() < 0) {
            throw new InvalidArgumentException('El precio base no puede ser negativo.');
        }

        if (!in_array($producto->getIva(), self::IVAS_VALIDOS, true)) {
            throw new InvalidArgumentException('IVA inválido (solo 4, 10 o 21).');
        }
    }

    private static function normalizarRutaImagen(string $ruta): string
    {
        $ruta = ltrim(trim($ruta), '/');
        if ($ruta === '') {
            return '';
        }

        if (!str_starts_with($ruta, self::DIR_BD_IMGS . '/')) {
            $ruta = self::DIR_BD_IMGS . '/' . basename($ruta);
        }

        return $ruta;
    }

    private static function borrarFicheroImagen(?string $ruta): void
    {
        $ruta = $ruta ? self::normalizarRutaImagen($ruta) : '';
        if ($ruta === '') {
            return;
        }

        $nombre = basename($ruta);
        if ($nombre === '') {
            return;
        }

        $path = RAIZ_APP . self::DIR_REL_IMGS . '/' . $nombre;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** @return string[] */
    public static function guardarImagenesDesdeUpload(?array $files): array
    {
        if (!$files || !isset($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $dir = RAIZ_APP . self::DIR_REL_IMGS;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de imágenes.');
        }

        $rutas = [];

        foreach ($files['name'] as $i => $nombreOriginal) {
            $error = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                foreach ($rutas as $rutaGuardada) {
                    self::borrarFicheroImagen($rutaGuardada);
                }
                throw new RuntimeException('Error al subir una de las imágenes.');
            }

            $tmp = (string)($files['tmp_name'][$i] ?? '');
            if ($tmp === '' || !is_file($tmp)) {
                foreach ($rutas as $rutaGuardada) {
                    self::borrarFicheroImagen($rutaGuardada);
                }
                throw new RuntimeException('Fichero temporal inválido en una de las imágenes.');
            }

            $mime = mime_content_type($tmp) ?: '';
            if (!in_array($mime, self::ALLOWED_MIMES, true)) {
                foreach ($rutas as $rutaGuardada) {
                    self::borrarFicheroImagen($rutaGuardada);
                }
                throw new RuntimeException('Formato de imagen no permitido (jpg, png, webp, gif).');
            }

            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
                default      => 'img',
            };

            $nombreFich = 'prod_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destino = $dir . '/' . $nombreFich;

            if (!move_uploaded_file($tmp, $destino)) {
                foreach ($rutas as $rutaGuardada) {
                    self::borrarFicheroImagen($rutaGuardada);
                }
                throw new RuntimeException('No se pudo guardar una de las imágenes.');
            }

            $rutas[] = self::DIR_BD_IMGS . '/' . $nombreFich;
        }

        return $rutas;
    }

    public static function crear(ProductoDTO $producto): int {
        self::validarProducto($producto);

        $idProducto = self::dao()->insert(
            $producto->getIdCategoria(),
            trim($producto->getNombre()),
            $producto->getDescripcion(),
            $producto->getPrecioBase(),
            $producto->getIva(),
            $producto->isDisponible(),
            $producto->isOfertado()
        );

        foreach ($producto->getImagenes() as $ruta) {
            $rutaNormalizada = self::normalizarRutaImagen((string)$ruta);
            if ($rutaNormalizada !== '') {
                self::dao()->addImagen($idProducto, $rutaNormalizada);
            }
        }

        return $idProducto;
    }

    public static function crearConUpload(ProductoDTO $producto, ?array $uploadFiles): int
    {
        $rutasImagenes = self::guardarImagenesDesdeUpload($uploadFiles);

        $productoConImagenes = new ProductoDTO(
            null,
            $producto->getIdCategoria(),
            $producto->getNombre(),
            $producto->getDescripcion(),
            $producto->getPrecioBase(),
            $producto->getIva(),
            $producto->isDisponible(),
            $producto->isOfertado(),
            $rutasImagenes
        );

        try {
            return self::crear($productoConImagenes);
        } catch (Throwable $e) {
            foreach ($rutasImagenes as $ruta) {
                self::borrarFicheroImagen($ruta);
            }
            throw $e;
        }
    }

    public static function actualizar(ProductoDTO $producto): bool
{
    $id = $producto->getId();
    if ($id === null || $id <= 0) {
        throw new InvalidArgumentException('ID inválido.');
    }

    self::validarProducto($producto);

    return self::dao()->update(
        $id,
        $producto->getIdCategoria(),
        trim($producto->getNombre()),
        $producto->getDescripcion(),
        $producto->getPrecioBase(),
        $producto->getIva(),
        $producto->isDisponible()
    );
}

    public static function actualizarConUpload(ProductoDTO $producto, ?array $uploadFiles): bool
    {
        $id = $producto->getId();
        if ($id === null || $id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $rutasImagenes = self::guardarImagenesDesdeUpload($uploadFiles);

        try {
            $ok = self::actualizar($producto);

            if (!$ok) {
                foreach ($rutasImagenes as $ruta) {
                    self::borrarFicheroImagen($ruta);
                }
                return false;
            }

            foreach ($rutasImagenes as $ruta) {
                $rutaNormalizada = self::normalizarRutaImagen((string)$ruta);
                if ($rutaNormalizada !== '') {
                    self::dao()->addImagen($id, $rutaNormalizada);
                }
            }

            return true;
        } catch (Throwable $e) {
            foreach ($rutasImagenes as $ruta) {
                self::borrarFicheroImagen($ruta);
            }
            throw $e;
        }
    }

    public static function borrarImagenes(int $idProducto, array $rutas): void
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        foreach ($rutas as $ruta) {
            $rutaNormalizada = self::normalizarRutaImagen((string)$ruta);
            if ($rutaNormalizada === '') {
                continue;
            }

            self::dao()->deleteImagenByRuta($idProducto, $rutaNormalizada);
            self::borrarFicheroImagen($rutaNormalizada);
        }
    }

    public static function retirarDeCarta(int $id): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        return self::dao()->setOfertado($id, false);
    }

    public static function ponerEnCarta(int $id): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        return self::dao()->setOfertado($id, true);
    }

    // Borrar
    public static function borrar(int $id): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $producto = self::obtener($id);
        if (!$producto) {
            throw new RuntimeException('Producto no encontrado.');
        }

        $imagenes = $producto->getImagenes();

        self::dao()->deleteImagenesByProducto($id);
        $ok = self::dao()->delete($id);

        if ($ok) {
            foreach ($imagenes as $ruta) {
                self::borrarFicheroImagen((string)$ruta);
            }
        }

        return $ok;
    }
}