<?php
require_once __DIR__ . '/../config.php';
require_once RAIZ_APP . '/includes/vistas/login/formularioRegistro.php';

$form = new FormularioRegistro();
$htmlFormRegistro = $form->gestiona();

$tituloPagina = 'Registro';

$urlLogin = RUTA_APP . '/includes/vistas/login.php';

$contenidoPrincipal = <<<HTML
<section id="contenido">
  <div class="auth-card">
    <h2 class="auth-title">BIENVENIDO</h2>

    <div class="auth-switch">
      <span class="auth-pill active">Registrarse</span>
      <a class="auth-pill" href="{$urlLogin}">Iniciar sesión</a>
    </div>

    <div class="auth-body">
      {$htmlFormRegistro}
    </div>
  </div>
</section>
HTML;

require RAIZ_APP . '/includes/vistas/common/plantilla.php';