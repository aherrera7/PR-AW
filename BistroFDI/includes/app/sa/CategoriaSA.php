<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/CategoriaDAO.php';

class CategoriaSA
{
    private static function dao(): CategoriaDAO
    {
        // config.php ya cargó Aplicacion.php e inicializó la app
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

    public static function crear(string $nombre, ?string $descripcion, ?string $imagen): int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');
        }
        return self::dao()->insert($nombre, $descripcion, $imagen);
    }

    public static function actualizar(int $id, string $nombre, ?string $descripcion, ?string $imagen): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        $nombre = trim($nombre);
        if ($nombre === '') throw new InvalidArgumentException('El nombre de la categoría no puede estar vacío.');

        return self::dao()->update($id, $nombre, $descripcion, $imagen);
    }

    public static function borrar(int $id): bool
    {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        return self::dao()->delete($id);
    }
}