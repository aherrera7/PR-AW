
document.addEventListener("DOMContentLoaded", () => {
    const contenedor = document.getElementById('productos-oferta-list');
    const btnAdd = document.getElementById('btn-add-producto');
    const inputDescuento = document.querySelector('input[name="descuento"]');
    const totalOriginalDisplay = document.getElementById('precio-total-original');
    const precioFinalDisplay = document.getElementById('precio-final-oferta');

    // Función para recalcular precios
    const calcularPrecios = () => {
        let totalOriginal = 0;
        document.querySelectorAll('.linea-producto').forEach(linea => {
            const select = linea.querySelector('select');
            const cant = linea.querySelector('input[type="number"]').value;
            const precio = select.options[select.selectedIndex]?.dataset.precio || 0;
            totalOriginal += parseFloat(precio) * parseInt(cant);
        });

        const dto = parseFloat(inputDescuento.value) || 0;
        const final = totalOriginal * (1 - dto);

        totalOriginalDisplay.textContent = totalOriginal.toFixed(2) + "€";
        precioFinalDisplay.textContent = final.toFixed(2) + "€";
    };

    // Añadir nueva fila de producto
    if(btnAdd) {
        btnAdd.addEventListener('click', () => {
            const index = contenedor.children.length;
            const template = contenedor.firstElementChild.cloneNode(true);
            template.querySelectorAll('input').forEach(i => i.value = 1);
            contenedor.appendChild(template);
            calcularPrecios();
        });
    }

    // Eventos para recalcular
    contenedor.addEventListener('change', calcularPrecios);
    inputDescuento.addEventListener('input', calcularPrecios);
});