<?php
require_once __DIR__ . '/includes/config.php';

$app = Aplicacion::getInstance();
$conn = $app->getConexionBd();

echo "<h2>Conexión correcta 🎉</h2>";

$result = $conn->query("SELECT id, nombre_usuario, rol FROM usuarios");

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

echo "<h3>Usuarios en la base de datos:</h3>";

while ($fila = $result->fetch_assoc()) {
    echo "ID: " . $fila['id'] . 
         " | Usuario: " . $fila['nombre_usuario'] . 
         " | Rol: " . $fila['rol'] . "<br>";
}