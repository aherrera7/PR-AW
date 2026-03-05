<?php
require_once __DIR__ . '/../config.php';

session_unset();
session_destroy();

header('Location: ' . RUTA_APP . '/index.php');
exit;