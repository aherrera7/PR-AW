<?php
/**
 * @var object $usuario
 * @var bool $esGerente
 * @var bool $editandoPropio
 * @var array $errores
 * @var array $opciones
 * @var string $avatarActual
 * @var string $avatarUrl
 * @var string $tituloPagina
 * @var string $btnCancelarUrl
 */
$erroresHtml = '';
if (!empty($errores)) {
  $erroresHtml .= '<ul class="errores">';
  foreach ($errores as $e) $erroresHtml .= '<li>'.h((string)$e).'</li>';
  $erroresHtml .= '</ul>';
}
?>

<section id="contenido">
  <h2><?= h($tituloPagina) ?></h2>
  <?= $erroresHtml ?>

  <form method="post" enctype="multipart/form-data">
    <div class="reg-avatar-row">
      <div class="reg-avatar-wrap">
        <img id="avatarPreview" src="<?= h($avatarUrl) ?>" alt="Avatar">
      </div>

      <div class="reg-avatar-controls">
        <div class="reg-line">
          <label for="avatarSelect">Avatares</label>
          <select id="avatarSelect" name="avatar_predef">
            <?php foreach ($opciones as $value => $label): ?>
              <option value="<?= h((string)$value) ?>" <?= ((string)$value === (string)$avatarActual) ? 'selected' : '' ?>>
                <?= h((string)$label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="reg-line">
          <label for="avatarFile">Subir</label>
          <input id="avatarFile" type="file" name="avatar_file" accept="image/*">
        </div>
      </div>
    </div>

    <div class="reg-grid">
      <div class="reg-field">
        <label>Usuario</label>
        <input type="text" name="nombreUsuario" value="<?= h((string)$usuario->getNombreUsuario()) ?>" required>
      </div>

      <div class="reg-field">
        <label>Email</label>
        <input type="email" name="email" value="<?= h((string)$usuario->getEmail()) ?>" required>
      </div>

      <div class="reg-field">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= h((string)$usuario->getNombre()) ?>" required>
      </div>

      <div class="reg-field">
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?= h((string)$usuario->getApellidos()) ?>" required>
      </div>
    </div>

    <div class="reg-submit">
      <button type="submit">Guardar cambios</button>
      <a class="perfil-btn" href="<?= h($btnCancelarUrl) ?>" style="margin-left:10px;">Cancelar</a>
    </div>

    <script>
      (function(){
        const IMG_BASE = "<?= h(RUTA_IMGS) ?>";
        const img = document.getElementById("avatarPreview");
        const sel = document.getElementById("avatarSelect");
        const file = document.getElementById("avatarFile");

        if(sel){
          sel.addEventListener("change", function(){
            img.src = IMG_BASE + "/" + sel.value;
          });
        }
        if(file){
          file.addEventListener("change", function(){
            const f = file.files[0];
            if(f) img.src = URL.createObjectURL(f);
          });
        }
      })();
    </script>
  </form>
</section>