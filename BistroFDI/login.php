<?php
require_once __DIR__ . '/includes/config.php';
require_once RAIZ_APP . '/includes/vistas/login/formularioLogin.php';

$form = new FormularioLogin();
$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Login';

$urlRegistrar = RUTA_APP . '/registrar.php';

$contenidoPrincipal = <<<HTML
<section id="contenido">
  <div class="auth-card">
    <h2 class="auth-title">BIENVENIDO</h2>

    <div class="auth-switch">
      <a class="auth-pill" href="{$urlRegistrar}">Registrarse</a>
      <span class="auth-pill active">Iniciar sesión</span>
    </div>

    <div class="auth-body">
      {$htmlFormLogin}
    </div>
  </div>
</section>
HTML;

require RAIZ_APP . '/includes/vistas/common/plantilla.php';