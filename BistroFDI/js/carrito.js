function cambiarCantidad(idProducto, cambio) {
    const input = document.getElementById('qty-' + idProducto);
    let nuevaCant = parseInt(input.value) + cambio;

    if (nuevaCant < 0) nuevaCant = 0;

    // Llamada asíncrona (AJAX) a la gestión del carrito
    fetch(`carrito_gestion.php?action=update&id=${idProducto}&cant=${nuevaCant}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            // Recargamos la página para que PHP recalcule totales y ofertas automáticamente
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}