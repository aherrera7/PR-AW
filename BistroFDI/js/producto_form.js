window.onload = function () {

    const pb = document.getElementById('precio_base');
    const iv = document.getElementById('iva');
    const pf = document.getElementById('precio_final');

    function actualizarPVP() {
        const base = parseFloat(pb.value) || 0;
        const iva = parseInt(iv.value, 10) || 0;
        const total = base * (1 + (iva / 100));

        pf.textContent = total.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + '€';
    }

    if (pb) pb.addEventListener('input', actualizarPVP);
    if (iv) iv.addEventListener('change', actualizarPVP);

    actualizarPVP();
};