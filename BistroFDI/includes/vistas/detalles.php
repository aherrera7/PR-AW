<?php
require_once __DIR__ . '/../config.php';

$tituloPagina = 'Detalles - Bistro FDI';

ob_start();
?>

<section>
  <h1>Detalles</h1>
  <h2>Introducción</h2>
  <p>
    Bistro FDI es una aplicación web diseñada para gestionar el funcionamiento de una cafetería.
    Permite a los clientes realizar sus pedidos de forma digital, con sus teléfonos móviles o portátiles,
    y hacer el seguimiento del estado de sus pedidos.
  </p>

  <p>
    Por otro lado, el sistema también ayuda al personal de la cafetería (cocineros, camareros y gerente)
    a organizar, preparar y gestionar los pedidos de manera eficiente, reduciendo los tiempos de espera
    y mejorando la coordinación entre cocina y sala.
  </p>
</section>

<section>
  <h2>Tipos de usuarios</h2>

  <section>
    <h3>Cliente</h3>
    <ul>
      <li>Registrarse e iniciar sesión.</li>
      <li>Consultar los productos y servicios de Bistro FDI.</li>
      <li>Aplicar ofertas y usar recompensas con BistroCoins.</li>
      <li>Realizar pedidos (en el local o para llevar).</li>
      <li>Consultar el estado de sus pedidos.</li>
    </ul>
  </section>

  <section>
    <h3>Camarero</h3>
    <ul>
      <li>Consultar los pedidos recibidos.</li>
      <li>Confirmar pagos realizados en persona.</li>
      <li>Preparar pedidos para su entrega.</li>
      <li>Marcar pedidos como entregados al cliente.</li>
    </ul>
  </section>

  <section>
    <h3>Cocinero</h3>
    <ul>
      <li>Tomar pedidos en estado “En preparación”.</li>
      <li>Preparar los productos del pedido.</li>
      <li>Marcar productos como listos.</li>
      <li>Finalizar pedidos cuando estén preparados.</li>
    </ul>
  </section>

  <section>
    <h3>Gerente</h3>
    <ul>
      <li>Gestionar usuarios y sus roles.</li>
      <li>Crear, modificar y eliminar categorías y productos.</li>
      <li>Gestionar ofertas y recompensas.</li>
      <li>Supervisar el estado de todos los pedidos del restaurante.</li>
    </ul>
  </section>
</section>

<section>
  <h2>Funcionalidades</h2>

  <section>
    <h3>Funcionalidad 0: Gestión de Usuarios</h3>
    <p>
      Permite crear, modificar y eliminar usuarios del sistema. Incluye registro de clientes,
      inicio de sesión, gestión de perfiles y asignación de roles por parte del gerente.
    </p>
  </section>

  <section>
    <h3>Funcionalidad 1: Gestión de Productos</h3>
    <p>
      El gerente puede crear, modificar, listar y eliminar (u ocultar) categorías y productos
      del restaurante, incluyendo imágenes, precios e IVA.
    </p>
  </section>

  <section>
    <h3>Funcionalidad 2: Gestión de Pedidos</h3>
    <p>
      Los clientes pueden crear pedidos seleccionando productos, elegir si es para comer en el
      local o para llevar, confirmar el pedido y pagar. El sistema gestiona los estados del pedido
      desde "Nuevo" hasta "Entregado".
    </p>
  </section>

  <section>
    <h3>Funcionalidad 3: Preparación de Pedidos</h3>
    <p>
      Los cocineros gestionan los pedidos en preparación, indicando cuándo están cocinando y
      cuándo el pedido está listo. El gerente puede supervisar todo el proceso.
    </p>
  </section>

  <section>
    <h3>Funcionalidad 4: Gestión de Ofertas</h3>
    <p>
      El gerente puede crear ofertas con descuentos sobre packs de productos. Los clientes pueden
      aplicar estas ofertas a sus pedidos cuando cumplan las condiciones.
    </p>
  </section>

  <section>
    <h3>Funcionalidad 5: Gestión de Recompensas</h3>
    <p>
      Los clientes ganan BistroCoins por cada euro gastado y pueden canjearlas por productos del menú.
      El gerente gestiona las recompensas disponibles.
    </p>
  </section>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require RAIZ_APP . '/includes/vistas/common/plantilla.php';