<?php
declare(strict_types=1);

function requireLogin(): void
{
    if (empty($_SESSION['login'])) {
        header('Location: ' . RUTA_APP . '/login.php');
        exit;
    }
}

function isGerente(): bool
{
    return !empty($_SESSION['esGerente']) && $_SESSION['esGerente'] === true;
}

function isCamarero(): bool
{
    return !empty($_SESSION['esCamarero']) && $_SESSION['esCamarero'] === true;
}

function isCocinero(): bool
{
    return !empty($_SESSION['esCocinero']) && $_SESSION['esCocinero'] === true;
}

function requireGerente(): void
{
    requireLogin();
    if (!isGerente()) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit;
    }
}

function requireStaff(): void
{
    requireLogin();
    if (!(isGerente() || isCamarero() || isCocinero())) {
        header('Location: ' . RUTA_APP . '/index.php');
        exit;
    }
}