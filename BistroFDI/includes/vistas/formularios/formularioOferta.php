<?php
declare(strict_types=1);

require_once RAIZ_APP . '/includes/vistas/common/formularioBase.php';
require_once RAIZ_APP . '/includes/app/sa/OfertaSA.php';
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

class FormularioOferta extends FormularioBase
{
    private ?int $idOferta;
    private ?OfertaDTO $oferta;

    public function __construct(?int $idOferta = null)
    {
        parent::__construct('formOferta', [
            'method' => 'POST',
            'urlRedireccion' => RUTA_VISTAS . '/gerente/ofertas_admin.php'
        ]);
        
        $this->idOferta = $idOferta !== null ? (int)$idOferta : null;
        $this->oferta = $this->idOferta ? OfertaSA::obtener($this->idOferta) : null;
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        // 1. Datos iniciales
        $nombre = $datos['nombre'] ?? ($this->oferta ? $this->oferta->getNombre() : '');
        $descripcion = $datos['descripcion'] ?? ($this->oferta ? $this->oferta->getDescripcion() : '');
        $fechaInicio = $datos['fecha_inicio'] ?? ($this->oferta ? $this->oferta->getFechaInicio() : date('Y-m-d'));
        $fechaFin = $datos['fecha_fin'] ?? ($this->oferta ? $this->oferta->getFechaFin() : date('Y-m-d', strtotime('+1 month')));
        $descuento = $datos['descuento'] ?? ($this->oferta ? $this->oferta->getDescuento() : 0.10);
        $activa = isset($datos['activa']) ? true : ($this->oferta ? $this->oferta->isActiva() : true);

        // 2. Lógica para Heredoc
        $checked = $activa ? 'checked' : '';
        $textoBoton = $this->idOferta ? 'Guardar Cambios' : 'Crear Oferta';
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);

        $todosLosProductos = ProductoSA::listar(); 
        $prodsIncluidos = $this->idOferta ? OfertaSA::obtenerProductosOferta($this->idOferta) : [];

        $html = <<<EOF
        $htmlErroresGlobales
        <input type="hidden" name="idOferta" value="{$this->idOferta}">
        
        <div class="grupo-control">
            <label>Nombre de la oferta:</label>
            <input type="text" name="nombre" value="{$nombre}" required>
        </div>

        <div class="grupo-control">
            <label>Descripción:</label>
            <textarea name="descripcion" rows="3">{$descripcion}</textarea>
        </div>

        <div style="display:flex; gap:20px;">
            <div class="grupo-control" style="flex:1;">
                <label>Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" value="{$fechaInicio}" required>
            </div>
            <div class="grupo-control" style="flex:1;">
                <label>Fecha Fin:</label>
                <input type="date" name="fecha_fin" value="{$fechaFin}" required>
            </div>
        </div>

        <div class="grupo-control">
            <label>Descuento (ej: 0.20 para un 20%):</label>
            <input type="number" step="0.01" min="0" max="0.99" name="descuento" value="{$descuento}" required>
        </div>

        <div class="grupo-control">
            <label>
                <input type="checkbox" name="activa" {$checked}> 
                Oferta disponible y activa
            </label>
        </div>

        <div class="oferta-productos-header">
            <h3>Productos en el pack</h3>
            <button type="button" id="btn-add-producto" class="btn">+ Añadir Producto</button>
        </div>

        <div id="productos-oferta-list">
EOF;

        $filas = !empty($prodsIncluidos) ? $prodsIncluidos : [null];

        foreach ($filas as $pIncluido) {
            $html .= '<div class="linea-producto">';
            $html .= '<select name="prod_ids[]" required>';
            $html .= '<option value="">Selecciona un producto...</option>';
            foreach ($todosLosProductos as $p) {
                $sel = ($pIncluido && $pIncluido->getIdProducto() == $p->getId()) ? 'selected' : '';
                
                // --- CORRECCIÓN AQUÍ: Usamos getPrecioFinal() que es el que existe en tu ProductoDTO ---
                $precio = $p->getPrecioFinal(); 
                
                $html .= "<option value='{$p->getId()}' data-precio='{$precio}' $sel>{$p->getNombre()} ({$precio}€)</option>";
            }
            $html .= '</select>';
            $html .= '<input type="number" name="prod_cants[]" value="' . ($pIncluido ? $pIncluido->getCantidad() : 1) . '" min="1" style="width:70px;">';
            $html .= '<button type="button" class="btn-borrar-fila" onclick="this.parentElement.remove(); window.calcularPrecios();">🗑️</button>';
            $html .= '</div>';
        }

        $html .= <<<EOF
        </div>

        <div class="resumen-precios">
            <p>Total original productos: <span id="precio-total-original">0.00€</span></p>
            <p><strong>Precio final con descuento: <span id="precio-final-oferta">0.00€</span></strong></p>
        </div>

        <div class="grupo-control" style="margin-top: 30px;">
            <button type="submit" class="btn-primario">{$textoBoton}</button>
        </div>
EOF;

        return $html;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $this->errores = [];
        $nombre = trim($datos['nombre'] ?? '');
        $fInicio = $datos['fecha_inicio'] ?? '';
        $fFin = $datos['fecha_fin'] ?? '';
        
        $productos = [];
        if (isset($datos['prod_ids'])) {
            foreach ($datos['prod_ids'] as $idx => $idProd) {
                if (!empty($idProd)) {
                    $productos[] = [
                        'id_producto' => (int)$idProd,
                        'cantidad' => (int)$datos['prod_cants'][$idx]
                    ];
                }
            }
        }

        if (empty($nombre)) $this->errores[] = "El nombre es obligatorio.";
        if ($fFin < $fInicio) $this->errores[] = "La fecha de fin no puede ser anterior a la de inicio.";
        if (empty($productos)) $this->errores[] = "Debes añadir al menos un producto a la oferta.";

        if (count($this->errores) === 0) {
            try {
                $descuento = (float)$datos['descuento'];
                $activa = isset($datos['activa']);
                $descripcion = $datos['descripcion'] ?? '';
                $id = !empty($datos['idOferta']) && $datos['idOferta'] !== '' ? (int)$datos['idOferta'] : null;

                if ($id) {
                    OfertaSA::actualizar($id, $nombre, $descripcion, $fInicio, $fFin, $descuento, $activa, $productos);
                } else {
                    OfertaSA::crear($nombre, $descripcion, $fInicio, $fFin, $descuento, $activa, $productos);
                }
            } catch (Exception $e) {
                $this->errores[] = "Error: " . $e->getMessage();
            }
        }
    }
}