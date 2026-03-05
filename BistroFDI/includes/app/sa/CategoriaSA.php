<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/CategoriaDAO.php';

class CategoriaSA
{
    private const DIR_REL_IMGS = '/img/categorias';
    private const ALLOWED_MIMES = ['image/jpeg','image/png','image/webp','image/gif'];

    private static function dao(): CategoriaDAO
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new CategoriaDAO($conn);
    }

    /** @return CategoriaDTO[] */
    public static function listar(): array
    {
        return self::dao()->findAll();
    }

    public static function obtener(int $id): ?CategoriaDTO
    {
        return self::dao()->findById($id);
    }

    public static function guardarImagenDesdeUpload(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir la imagen.');
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp)) {
            throw new RuntimeException('Fichero temporal inválido.');
        }

        $mime = @mime_content_type($tmp) ?: '';
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('Formato no permitido (jpg, png, webp, gif).');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'img',
        };

        $dir = RAIZ_APP . self::DIR_REL_IMGS;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el directorio de imágenes.');
        }

        $nombreFich = 'cat_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $nombreFich;

        if (!move_uploaded_file($tmp, $dest)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }

        return $nombreFich;
    }

    private static function borrarFicheroImagen(?string $imagen): void
    {
        $imagen = $imagen ? trim($imagen) : '';
        if ($imagen === '') return;

        // evita rutas raras
        $imagen = basename($imagen);

        $path = RAIZ_APP . self::DIR_REL_IMGS . '/' . $imagen;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function crear(string $nombre, ?string $descripcion, ?string $imagen): int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }

        return self::dao()->insert($nombre, $descripcion, $imagen);
    }

    public static function crearConUpload(string $nombre, ?string $descripcion, ?array $uploadFile): int
    {
        $imagen = self::guardarImagenDesdeUpload($uploadFile);
        try {
            return self::crear($nombre, $descripcion, $imagen);
        } catch (Throwable $e) {
            if ($imagen) self::borrarFicheroImagen($imagen);
            throw $e;
        }
    }

    public static function actualizar(int $id, string $nombre, ?string $descripcion, ?string $imagen): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        $nombre = trim($nombre);
        if ($nombre === '') throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');

        return self::dao()->update($id, $nombre, $descripcion, $imagen);
    }

    public static function actualizarConUpload(int $id, string $nombre, ?string $descripcion, ?array $uploadFile): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');

        $cat = self::obtener($id);
        if (!$cat) {
            throw new RuntimeException('Categoría no encontrada.');
        }

        $imagenActual = $cat->getImagen();
        $nuevaImagen = self::guardarImagenDesdeUpload($uploadFile);
        $imagenFinal = $nuevaImagen ?? $imagenActual;

        try {
            $ok = self::actualizar($id, $nombre, $descripcion, $imagenFinal);

            if ($ok && $nuevaImagen && $imagenActual) {
                self::borrarFicheroImagen($imagenActual);
            }

            return $ok;
        } catch (Throwable $e) {
            if ($nuevaImagen) self::borrarFicheroImagen($nuevaImagen);
            throw $e;
        }
    }

    public static function borrar(int $id): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');

        $cat = self::obtener($id);
        if (!$cat) {
            throw new RuntimeException('Categoría no encontrada.');
        }

        $imagen = $cat->getImagen();

        $ok = self::dao()->delete($id);
        if ($ok) {
            self::borrarFicheroImagen($imagen);
        }
        return $ok;
    }
}