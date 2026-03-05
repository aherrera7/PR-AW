<!DOCTYPE html>
<html>
    <head>
        <title><?= $tituloPagina ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <link rel="stylesheet" type="text/css" href="<?php echo RUTA_CSS . '/estilo.css' ?>" />
        <link rel="stylesheet" href="<?= RUTA_CSS . '/gerente.css' ?>" />
    </head>
    <?php
        $staffTheme =
            !empty($_SESSION['login']) &&
            (
                (!empty($_SESSION['esGerente'])  && $_SESSION['esGerente'] === true) ||
                (!empty($_SESSION['esCocinero']) && $_SESSION['esCocinero'] === true) ||
                (!empty($_SESSION['esCamarero']) && $_SESSION['esCamarero'] === true)
            );
    ?>
    <body class="<?= $staffTheme ? 'staff' : '' ?>">
        <div id="contenedor">
            <?php
                include(RAIZ_APP . '/includes/vistas/common/header.php');
                include(RAIZ_APP . '/includes/vistas/common/nav.php');
            ?>

            <div class="contenido">
                <main>
                    <?= $contenidoPrincipal ?? '' ?>
                </main>

                <?php
                    include(RAIZ_APP . '/includes/vistas/common/aside.php');
                ?>
            </div>

            <?php
                include(RAIZ_APP . '/includes/vistas/common/footer.php');
            ?>
        </div>

        <script src="<?= RUTA_JS ?>/menu.js"></script>
    </body>
</html>