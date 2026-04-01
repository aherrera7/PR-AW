<?php
require_once __DIR__ . '/includes/config.php'; // Asegúrate de que la ruta a config sea correcta
require_once RAIZ_APP . '/includes/app/sa/ProductoSA.php';

echo "<h2>Prueba de Integración: Campo es_cocina</h2>";

try {
    // 1. Probar la lectura (findAll)
    echo "<h3>1. Listando productos y su estado de cocina:</h3>";
    $productos = ProductoSA::listar();
    
    echo "<ul>";
    foreach (array_slice($productos, 0, 5) as $p) { // Probamos con los 5 primeros
        $estadoCocina = $p->getEsCocina() ? "✅ COCINA" : "🥤 BARRA/BEBIDA";
        echo "<li><strong>{$p->getNombre()}:</strong> $estadoCocina</li>";
    }
    echo "</ul>";

    // 2. Probar la obtención individual (findById)
    if (!empty($productos)) {
        $idTest = $productos[0]->getId();
        echo "<h3>2. Probando obtención individual (ID: $idTest):</h3>";
        $pIndividual = ProductoSA::obtener($idTest);
        echo "Nombre: " . $pIndividual->getNombre() . " | Es cocina: " . ($pIndividual->getEsCocina() ? 'SÍ' : 'NO');
    }

} catch (Exception $e) {
    echo "<div style='color:red;'><strong>ERROR:</strong> " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}