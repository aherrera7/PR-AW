<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/app/dao/OfertaDAO.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

class OfertaSA {
    private static function dao(): OfertaDAO
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        return new OfertaDAO($conn);
    }

    public static function obtener(int $id): ?OfertaDTO
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de oferta inválido.');
        }

        return self::dao()->findById($id);
    }

    public static function listarTodas(): array
    {
        return self::dao()->findAll();
    }

    public static function listarActivasHoy(): array
    {
        return self::dao()->findActivasHoy();
    }

    public static function obtenerProductosOferta(int $idOferta): array
    {
        if ($idOferta <= 0) {
            throw new InvalidArgumentException('ID de oferta inválido.');
        }

        return self::dao()->findProductosByOferta($idOferta);
    }

    public static function crear(
        string $nombre,
        string $descripcion,
        string $fechaInicio,
        string $fechaFin,
        float $descuento,
        bool $activa,
        array $productos
    ): int {
        self::validarDatosOferta($nombre, $descripcion, $fechaInicio, $fechaFin, $descuento, $productos);

        $idOferta = self::dao()->insertOferta(
            $nombre,
            $descripcion,
            $fechaInicio,
            $fechaFin,
            $descuento,
            $activa
        );

        foreach ($productos as $linea) {
            $idProducto = intval(strval($linea['id_producto'] ?? '0'));
            $cantidad = intval(strval($linea['cantidad'] ?? '0'));

            self::dao()->insertOfertaProducto($idOferta, $idProducto, $cantidad);
        }

        return $idOferta;
    }

    public static function actualizar(
        int $id,
        string $nombre,
        string $descripcion,
        string $fechaInicio,
        string $fechaFin,
        float $descuento,
        bool $activa,
        array $productos
    ): bool {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de oferta inválido.');
        }

        self::validarDatosOferta($nombre, $descripcion, $fechaInicio, $fechaFin, $descuento, $productos);

        self::dao()->updateOferta(
            $id,
            $nombre,
            $descripcion,
            $fechaInicio,
            $fechaFin,
            $descuento,
            $activa
        );

        self::dao()->deleteOfertaProductos($id);

        foreach ($productos as $linea) {
            $idProducto = intval(strval($linea['id_producto'] ?? '0'));
            $cantidad = intval(strval($linea['cantidad'] ?? '0'));

            self::dao()->insertOfertaProducto($id, $idProducto, $cantidad);
        }

        return true;
    }

    public static function borrar(int $id): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de oferta inválido.');
        }

        return self::dao()->deleteOferta($id);
    }

    public static function estaDisponibleHoy(OfertaDTO $oferta): bool
    {
        if (!$oferta->isActiva()) {
            return false;
        }

        $hoy = date('Y-m-d');

        return $hoy >= $oferta->getFechaInicio() && $hoy <= $oferta->getFechaFin();
    }

    public static function calcularPrecioPack(int $idOferta): float
    {
        $lineas = self::obtenerProductosOferta($idOferta);

        if (empty($lineas)) {
            throw new InvalidArgumentException('La oferta no tiene productos.');
        }

        $total = 0.0;

        foreach ($lineas as $linea) {
            $producto = ProductoSA::obtener($linea->getIdProducto());

            if ($producto === null) {
                throw new InvalidArgumentException('Producto no existente en la oferta.');
            }

            if (!$producto->isDisponible()) {
                throw new InvalidArgumentException('La oferta contiene un producto no disponible.');
            }

            $total += $producto->getPrecioFinal() * $linea->getCantidad();
        }

        return round($total, 2);
    }

    public static function calcularAplicacionOferta(int $idOferta, array $carrito): array
    {
        $oferta = self::obtener($idOferta);

        if ($oferta === null) {
            return [
                'aplicable' => false,
                'motivo' => 'La oferta no existe.'
            ];
        }

        if (!self::estaDisponibleHoy($oferta)) {
            return [
                'aplicable' => false,
                'motivo' => 'La oferta no está disponible actualmente.'
            ];
        }

        $lineas = self::obtenerProductosOferta($idOferta);

        if (empty($lineas)) {
            return [
                'aplicable' => false,
                'motivo' => 'La oferta no tiene productos.'
            ];
        }

        $vecesAplicable = null;
        $precioPack = 0.0;

        foreach ($lineas as $linea) {
            $idProducto = $linea->getIdProducto();
            $cantidadNecesaria = $linea->getCantidad();
            $cantidadEnCarrito = intval(strval($carrito[$idProducto] ?? '0'));

            if ($cantidadEnCarrito < $cantidadNecesaria) {
                return [
                    'aplicable' => false,
                    'motivo' => 'No se cumplen las cantidades necesarias de la oferta.'
                ];
            }

            $producto = ProductoSA::obtener($idProducto);

            if ($producto === null) {
                return [
                    'aplicable' => false,
                    'motivo' => 'Existe un producto no válido en la oferta.'
                ];
            }

            $precioPack += $producto->getPrecioFinal() * $cantidadNecesaria;

            $vecesProducto = intdiv($cantidadEnCarrito, $cantidadNecesaria);

            if ($vecesAplicable === null || $vecesProducto < $vecesAplicable) {
                $vecesAplicable = $vecesProducto;
            }
        }

        if ($vecesAplicable === null || $vecesAplicable <= 0) {
            return [
                'aplicable' => false,
                'motivo' => 'La oferta no es aplicable.'
            ];
        }

        $precioPack = round($precioPack, 2);
        $descuentoUnitario = round($precioPack * $oferta->getDescuento(), 2);
        $descuentoTotal = round($descuentoUnitario * $vecesAplicable, 2);
        $totalSinDescuento = round($precioPack * $vecesAplicable, 2);
        $totalConDescuento = round($totalSinDescuento - $descuentoTotal, 2);

        return [
            'aplicable' => true,
            'id_oferta' => $oferta->getId(),
            'nombre_oferta' => $oferta->getNombre(),
            'veces' => $vecesAplicable,
            'precio_pack' => $precioPack,
            'descuento_por_pack' => $descuentoUnitario,
            'descuento_total' => $descuentoTotal,
            'total_sin_descuento_oferta' => $totalSinDescuento,
            'total_con_descuento_oferta' => $totalConDescuento
        ];
    }

    public static function obtenerMejorOfertaAplicable(array $carrito): ?array
    {
        $ofertas = self::listarActivasHoy();
        $mejor = null;

        foreach ($ofertas as $oferta) {
            $resultado = self::calcularAplicacionOferta($oferta->getId(), $carrito);

            if (!($resultado['aplicable'] ?? false)) {
                continue;
            }

            if ($mejor === null) {
                $mejor = $resultado;
                continue;
            }

            $descuentoActual = floatval(strval($resultado['descuento_total'] ?? '0'));
            $descuentoMejor = floatval(strval($mejor['descuento_total'] ?? '0'));

            if ($descuentoActual > $descuentoMejor) {
                $mejor = $resultado;
            }
        }

        return $mejor;
    }

    private static function validarDatosOferta(
        string $nombre,
        string $descripcion,
        string $fechaInicio,
        string $fechaFin,
        float $descuento,
        array $productos
    ): void {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException('El nombre de la oferta es obligatorio.');
        }

        if (trim($descripcion) === '') {
            throw new InvalidArgumentException('La descripción de la oferta es obligatoria.');
        }

        if (!self::esFechaValida($fechaInicio) || !self::esFechaValida($fechaFin)) {
            throw new InvalidArgumentException('Las fechas de la oferta no son válidas.');
        }

        if ($fechaFin < $fechaInicio) {
            throw new InvalidArgumentException('La fecha de fin no puede ser anterior a la fecha de inicio.');
        }

        if ($descuento <= 0 || $descuento >= 1) {
            throw new InvalidArgumentException('El descuento debe ser mayor que 0 y menor que 1.');
        }

        if (empty($productos)) {
            throw new InvalidArgumentException('La oferta debe incluir al menos un producto.');
        }

        foreach ($productos as $linea) {
            $idProducto = intval(strval($linea['id_producto'] ?? '0'));
            $cantidad = intval(strval($linea['cantidad'] ?? '0'));

            if ($idProducto <= 0) {
                throw new InvalidArgumentException('Hay un producto inválido en la oferta.');
            }

            if ($cantidad <= 0) {
                throw new InvalidArgumentException('La cantidad de un producto de la oferta no es válida.');
            }

            $producto = ProductoSA::obtener($idProducto);
            if ($producto === null) {
                throw new InvalidArgumentException('Uno de los productos de la oferta no existe.');
            }
        }
    }

    private static function esFechaValida(string $fecha): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if ($dt === false) {
            return false;
        }

        return $dt->format('Y-m-d') === $fecha;
    }
}