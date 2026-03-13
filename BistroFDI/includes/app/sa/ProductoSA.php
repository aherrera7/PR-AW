<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/ProductoDAO.php';

class ProductoSA
{
    private const IVAS_VALIDOS = [4, 10, 21];

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

    public static function crear(
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        bool $disponible,
        bool $ofertado,
        array $rutasImagenes = []
    ): int {
        self::validarProducto($idCategoria, $nombre, $precioBase, $iva);

        $idProducto = self::dao()->insert(
            $idCategoria,
            trim($nombre),
            $descripcion,
            $precioBase,
            $iva,
            $disponible,
            $ofertado
        );

        foreach ($rutasImagenes as $ruta) {
            $ruta = ltrim(trim((string)$ruta), '/');
            if ($ruta !== '') {
                self::dao()->addImagen($idProducto, $ruta);
            }
        }

        return $idProducto;
    }

    public static function actualizar(
        int $id,
        int $idCategoria,
        string $nombre,
        ?string $descripcion,
        float $precioBase,
        int $iva,
        bool $disponible,
        array $rutasImagenesNuevas = []
    ): bool {
        if ($id <= 0) throw new InvalidArgumentException('ID inválido.');
        self::validarProducto($idCategoria, $nombre, $precioBase, $iva);

        $ok = self::dao()->update(
            $id,
            $idCategoria,
            trim($nombre),
            $descripcion,
            $precioBase,
            $iva,
            $disponible
        );

        if (!$ok) return false;

        foreach ($rutasImagenesNuevas as $ruta) {
            $ruta = ltrim(trim((string)$ruta), '/');
            if ($ruta !== '') {
                self::dao()->addImagen($id, $ruta);
            }
        }

        return true;
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

    private static function validarProducto(int $idCategoria, string $nombre, float $precioBase, int $iva): void
    {
        if ($idCategoria <= 0) throw new InvalidArgumentException('Categoría inválida.');

        $nombre = trim($nombre);
        if ($nombre === '') throw new InvalidArgumentException('El nombre del producto no puede estar vacío.');

        if ($precioBase < 0) throw new InvalidArgumentException('El precio base no puede ser negativo.');

        if (!in_array($iva, self::IVAS_VALIDOS, true)) {
            throw new InvalidArgumentException('IVA inválido (solo 4, 10 o 21).');
        }
    }
}