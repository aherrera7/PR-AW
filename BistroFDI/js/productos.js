window.onload = function () {

    console.log("JS cargado"); // para comprobar

    // Navegación de imágenes
    document.querySelectorAll("[data-nav-img]").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            const d = parseInt(this.dataset.dir, 10);

            const imgs = document.querySelectorAll('.img-carrusel-' + id);
            if (!imgs.length) return;

            let cur = 0;

            imgs.forEach((img, i) => {
                if (!img.classList.contains('is-hidden')) {
                    cur = i;
                }
            });

            imgs[cur].classList.add('is-hidden');
            imgs[(cur + d + imgs.length) % imgs.length].classList.remove('is-hidden');
        });
    });

    // Cantidad
    document.querySelectorAll("[data-cant]").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            const d = parseInt(this.dataset.dir, 10);

            const input = document.getElementById('cant-' + id);
            if (!input) return;

            let v = parseInt(input.value, 10) || 1;
            v += d;

            if (v >= 1) {
                input.value = v;
            }
        });
    });

    // Añadir carrito
    document.querySelectorAll("[data-add]").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            const logueado = this.dataset.logueado === "1";

            if (!logueado) {
                alert('Debes iniciar sesión para pedir.');
                window.location.href = '/login.php';
                return;
            }

            const input = document.getElementById('cant-' + id);
            const c = input ? input.value : 1;

            window.location.href = 'carrito_gestion.php?action=add&id=' + id + '&cant=' + c;
        });
    });

};