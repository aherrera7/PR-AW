window.onload = function () {

    const radios = document.querySelectorAll('input[name="metodo_pago"]');
    const bloqueTarjeta = document.getElementById('bloque-tarjeta');
    const bloqueEfectivo = document.getElementById('bloque-efectivo');

    function actualizarVistaPago() {

        const seleccionado = document.querySelector('input[name="metodo_pago"]:checked');

        bloqueTarjeta.classList.remove('activo');
        bloqueEfectivo.classList.remove('activo');

        if (!seleccionado) return;

        if (seleccionado.value === 'tarjeta') {
            bloqueTarjeta.classList.add('activo');
        } else if (seleccionado.value === 'efectivo') {
            bloqueEfectivo.classList.add('activo');
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', actualizarVistaPago);
    });

    actualizarVistaPago();
};