<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Contacto - Bistro FDI';

ob_start();
?>

<h1>Contacto</h1>
<p>Por favor, rellena el siguiente formulario para enviarnos tus dudas o sugerencias:</p>

<form action="mailto:correo@ejemplo.com" method="post" enctype="text/plain">
  <fieldset>
    <legend>Información de contacto:</legend>
    <p>Nombre: <input type="text" name="nombre" required></p>
    <p>Email: <input type="email" name="email" required></p>
  </fieldset>

  <fieldset>
    <legend>Motivo de la consulta:</legend>
    <p>
      <input type="radio" name="motivo" value="evaluacion" checked> Evaluación<br>
      <input type="radio" name="motivo" value="sugerencias"> Sugerencias<br>
      <input type="radio" name="motivo" value="criticas"> Críticas
    </p>
  </fieldset>

  <fieldset>
    <legend>Su consulta:</legend>
    <p>Escriba aquí su mensaje:</p>
    <textarea name="consulta" rows="5" cols="50" required></textarea>

    <p>
      <input type="checkbox" name="terminos" required>
      Marque esta casilla para verificar que ha leído nuestros términos y condiciones del servicio
    </p>
  </fieldset>

  <div class="form-actions">
  <button class="btn btn-primary" type="submit" name="botonSend">Enviar correo</button>
  <button class="btn" type="reset" name="botonReset">Limpiar formulario</button>
  </div>
</form>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';