<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Planificacion - Bistro FDI';

ob_start();
?>

<?php
?>
<section>
    <h1>Planificación</h1>
    <table>
        <caption><strong>PRÁCTICA 1</strong></caption>
        <tr><th colspan="2">REPARTO</th></tr>
        <tr>
            <th>NOMBRE</th>
            <th>TRABAJO REALIZADO</th>
        </tr>
        <tr>
            <td>ALBA HERRERA OLIVA</td>
            <td>detalles.html <p>bocetos.html</p></td>
        </tr>
        <tr>
            <td>ÓSCAR GONZÁLEZ DE DIOS</td>
            <td>contacto.html</td>
        </tr>
        <tr>
            <td>ADRIANA MUNICIO IZQUIERDO</td>
            <td>index.html <p>miembros.html</p></td>
        </tr>
        <tr>
            <td>NURIA OVIEDO MARTÍN</td>
            <td>planificación.html</td>
        </tr>
    </table>
</section>

<br><br>

<section>
    <table>
        <caption><strong>HITOS Y FECHAS DE TERMINACIÓN</strong></caption>
        <tr><th colspan="3">Entregas</th></tr>
        <tr>
            <th>Número de entrega</th>
            <th>Descripción</th>
            <th>Fecha a entregar</th>
        </tr>
        <tr>
            <td>Práctica Uno</td>
            <td>Descripción detallada del proyecto en forma de un Sitio Web simple con varios documentos descriptivos...</td>
            <td>13/02/2026</td>
        </tr>
        <tr>
            <td>Práctica Dos</td>
            <td>Diseño de la aplicación usando HTML (sin CSS), arquitectura del lado del servidor y prototipo funcional...</td>
            <td>06/03/2026</td>
        </tr>
        <tr>
            <td>Práctica Tres</td>
            <td>Diseño de la apariencia de la aplicación usando hojas de estilos e incremento de la funcionalidad...</td>
            <td>08/04/2026</td>
        </tr>
        <tr>
            <td>Entrega Final</td>
            <td>Aplicación Web completa y funcional...</td>
            <td>03/05/2026</td>
        </tr>
    </table>
</section>

<br>

<section>
    <h2>Gantt Tareas</h2>
    <img src="<?= RUTA_IMGS ?>/Gantt.png" alt="Gantt tareas" width="1390">
    <p>Gantt con las secuencia de prácticas y fechas de terminación</p>
</section>


<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';

