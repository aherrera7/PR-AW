<!DOCTYPE html>
<html>
    <head>
        <title><?= $tituloPagina ?></title>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="<?php echo RUTA_CSS . '/estilo.css' ?>" />
    </head>
    <body>
        <div id="contenedor">
            <?php
                include(RAIZ_APP . '/includes/vistas/common/header.php');
                include(RAIZ_APP . '/includes/vistas/common/nav.php');
            ?>

            <main>
                <?= $contenidoPrincipal ?? '' ?>
            </main>

            <?php
                include(RAIZ_APP . '/includes/vistas/common/aside.php');
                include(RAIZ_APP . '/includes/vistas/common/footer.php');
            ?>
        </div> 
        <script src="<?= RUTA_JS ?>/menu.js"></script>
    </body>
</html>