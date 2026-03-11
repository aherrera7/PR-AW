<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Contacto - Bistro FDI';

ob_start();
?>

<section id="contenido">
  <h1>Contacto</h1>

  <p>Por favor, rellena el siguiente formulario para enviarnos tus dudas o sugerencias:</p>

  <div class="card">
    <form action="mailto:correo@ejemplo.com" method="post" enctype="text/plain" class="stack">

      <fieldset class="stack">
        <legend>Información de contacto</legend>

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Email</label>
        <input type="email" name="email" required>
      </fieldset>

      <fieldset class="stack">
        <legend>Motivo de la consulta</legend>

        <label><input type="radio" name="motivo" value="evaluacion" checked> Evaluación</label>
        <label><input type="radio" name="motivo" value="sugerencias"> Sugerencias</label>
        <label><input type="radio" name="motivo" value="criticas"> Críticas</label>
      </fieldset>

      <fieldset class="stack">
        <legend>Su consulta</legend>

        <label>Escriba aquí su mensaje</label>
        <textarea name="consulta" rows="5" required></textarea>

        <label>
          <input type="checkbox" name="terminos" required>
          Marque esta casilla para verificar que ha leído nuestros términos y condiciones del servicio
        </label>
      </fieldset>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit" name="botonSend">Enviar correo</button>
        <button class="btn" type="reset" name="botonReset">Limpiar formulario</button>
      </div>

    </form>
  </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';