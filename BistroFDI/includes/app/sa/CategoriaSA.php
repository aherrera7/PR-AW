<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/CategoriaDAO.php';
require_once RAIZ_APP . '/includes/app/dto/CategoriaDTO.php';

class CategoriaSA
{
    private const DIR_REL_IMGS = '/img/categorias';
    private const DIR_BD_IMGS  = 'categorias';
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

        return self::DIR_BD_IMGS . '/' . $nombreFich;
    }

    private static function borrarFicheroImagen(?string $imagen): void
    {
        $imagen = $imagen ? trim($imagen) : '';
        if ($imagen === '') return;

        $imagen = ltrim(str_replace('\\', '/', $imagen), '/');

        $prefijo = self::DIR_BD_IMGS . '/';
        if (str_starts_with($imagen, $prefijo)) {
            $imagen = substr($imagen, strlen($prefijo));
        }

        $imagen = basename($imagen);
        if ($imagen === '') return;

        $path = RAIZ_APP . self::DIR_REL_IMGS . '/' . $imagen;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function validarDatos(CategoriaDTO $categoria): void
    {
        $nombre = trim($categoria->getNombre());
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }
    }

    public static function crear(CategoriaDTO $categoria): int
    {
        self::validarDatos($categoria);

        return self::dao()->insert(
            trim($categoria->getNombre()),
            $categoria->getDescripcion(),
            $categoria->getImagen()
        );
    }

    public static function crearConUpload(CategoriaDTO $categoria, ?array $uploadFile): int
    {
        $imagen = self::guardarImagenDesdeUpload($uploadFile);

        $categoriaConImagen = new CategoriaDTO(
            null,
            $categoria->getNombre(),
            $categoria->getDescripcion(),
            $imagen
        );

        try {
            return self::crear($categoriaConImagen);
        } catch (Throwable $e) {
            if ($imagen) {
                self::borrarFicheroImagen($imagen);
            }
            throw $e;
        }
    }

    public static function actualizar(CategoriaDTO $categoria): bool
    {
        $id = $categoria->getId();
        if ($id === null || $id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }
       
        self::validarDatos($categoria);

        return self::dao()->update(
            $id,
            trim($categoria->getNombre()),
            $categoria->getDescripcion(),
            $categoria->getImagen()
        );        
    }

    public static function actualizarConUpload(CategoriaDTO $categoria, ?array $uploadFile): bool
    {
        $id = $categoria->getId();
        if ($id === null || $id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $actual = self::obtener($id);
        if (!$actual) {
            throw new RuntimeException('Categoría no encontrada.');
        }

        $imagenActual = $actual->getImagen();
        $nuevaImagen = self::guardarImagenDesdeUpload($uploadFile);
        $imagenFinal = $nuevaImagen ?? $imagenActual;

        $categoriaFinal = new CategoriaDTO(
            $id,
            $categoria->getNombre(),
            $categoria->getDescripcion(),
            $imagenFinal
        );

        try {
            $ok = self::actualizar($categoriaFinal);

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