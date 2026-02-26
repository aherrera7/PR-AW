    <section>
        <h1>Bocetos</h1>
        <p>
        En esta sección se presentan los bocetos de las distintas pantallas que conforman la aplicación web
        <strong>Bistro FDI</strong>. Estos bocetos representan la estructura visual y funcional de la aplicación,
        mostrando cómo interactúan los distintos tipos de usuarios con el sistema.
        </p>

        <p>
        Con el objetivo de mejorar la usabilidad y diferenciar claramente los roles, se ha optado por un
        diseño visual diferenciado: las pantallas destinadas a los clientes utilizan un fondo de color blanco,
        mientras que las pantallas destinadas a empleados (camareros, cocineros y gerente) emplean un fondo
        gris oscuro. De este modo, se facilita la identificación del tipo de usuario y se mejora la experiencia
        de uso de la aplicación.
        </p>
     </section>

     <section>
        <figure>
        <img src="img/Bocetos/Bcto1_Inicio.png" alt="Boceto de la página de inicio">
        <figcaption>
            <p><strong>Descripción del Boceto 1:</strong> En esta pantalla se muestra la página de inicio de la aplicación Bistro FDI. Desde aquí, el usuario puede seleccionar 
            si desea realizar un pedido para consumir en el local o para llevar. El usuario accede a esta pantalla cuando entra por primera vez en la aplicación o 
            cuando pulsa el botón “Inicio” de la barra de navegación.</p>

            <p>Para iniciar sesión o registrarse, el usuario o trabajador debe pulsar el botón situado en la esquina superior izquierda (“Login / Register”), 
            que lo redirige a la pantalla de autenticación (Boceto 2).</p>

            <p>Este boceto puede ser utilizado por cualquier tipo de usuario y está diseñado para ser intuitivo, claro y 
            fácil de usar desde cualquier dispositivo.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto2_Login-Reg.png" alt="Boceto de Login/register">
        <figcaption>
            <p><strong>Descripción del Boceto 2:</strong> En esta pantalla el usuario puede iniciar sesión o acceder al proceso de registro, dependiendo del botón que seleccione.</p>

            <p>El boceto mostrado corresponde a la pantalla de inicio de sesión. Si el usuario pulsa el botón de registro, 
            será redirigido a la pantalla de creación de cuenta (Boceto 3).</p>

            <p>Este boceto puede ser utilizado por cualquier tipo de usuario. Por defecto, todos los nuevos usuarios se registran con el rol de cliente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto3_Register.png" alt="Boceto de register">
        <figcaption>
            <p><strong>Descripción del Boceto 3:</strong>  En esta pantalla el usuario puede registrarse en la aplicación rellenando el formulario con toda la información requerida 
            (nombre de usuario, email, contraseña, etc.).</p>

            <p>Al pulsar el botón de registro, el usuario quedará dado de alta en el sistema y será redirigido automáticamente a la pantalla de su perfil personal 
            (Boceto 4).</p>

            <p>Este boceto está disponible para cualquier usuario que aún no tenga cuenta en la aplicación.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto4_Perfil.png" alt="Boceto del perfil del usuario">
        <figcaption>
            <p><strong>Descripción del Boceto 4:</strong>  En esta pantalla se muestra la información del perfil del usuario. Desde aquí, el usuario puede modificar sus datos personales, 
            cambiar su avatar o actualizar su contraseña.</p>

            <p>El rol del usuario y el número de BistroCoins no pueden modificarse desde esta vista, ya que el rol solo puede ser gestionado por el gerente y 
            las BistroCoins se actualizan automáticamente.</p>

            <p>También se puede acceder a esta pantalla pulsando el botón “Mi Perfil” situado en la parte superior derecha de la aplicación.
            Este boceto puede ser utilizado por cualquier tipo de usuario.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto5_Producto.png" alt="Boceto del producto">
        <figcaption>
            <p><strong>Descripción del Boceto 5:</strong> En esta pantalla se muestra la vista detallada de un producto. Se visualiza información como la descripción, la categoría, 
            el precio base, el IVA aplicado y su disponibilidad.</p>

            <p>Para acceder a este pantalla, pueden pulsar cualquier producto mientras esté en la carta (Boceto 6). 
                O si se trata del gerente, pulsará las tres lineas (más opciones) en la barra de navegación superior, en el apartado de "Productos" </p>

            <p>El gerente dispone de opciones adicionales, como modificar el producto, aplicar ofertas o gestionar la reposición, que no están disponibles 
            para el resto de usuarios. Este boceto solo puede ser utilizado por usuarios con rol de gerente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto6_Carta.png" alt="Boceto de la carta">
        <figcaption>
            <p><strong>Descripción del Boceto 6:</strong>  En esta pantalla se muestra la carta de productos disponibles del Bistro FDI, organizada por categorías. 
            El usuario puede consultar los productos, ver sus descripciones, precios e imágenes.</p>

            <p>Se puede acceder a esta vista desde la pantalla de inicio al comenzar un pedido o desde la sección de pedidos al crear uno nuevo.
            Para continuar con el proceso de compra, el usuario puede acceder al carrito pulsando el icono correspondiente (Boceto 7).</p>

            <p>Este boceto puede ser utilizado por cualquier tipo de usuario. Solo los clientes pueden añadir productos al pedido, mientras que los gerentes pueden gestionar los productos.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto7_Carrito.png" alt="Boceto del carrito">
        <figcaption>
            <p><strong>Descripción del Boceto 7:</strong> En esta pantalla se muestra el carrito del cliente, donde aparecen los productos seleccionados junto con sus cantidades y 
            el precio total del pedido.</p>

            <p>Desde aquí, el cliente puede modificar las cantidades, confirmar el pedido o cancelarlo. Al confirmar el pedido, el usuario será redirigido 
            al proceso de pago.</p>

            <p>Una vez realizado el pedido, el cliente puede acceder a la sección de "Pedidos" desde la barra de navegación para consultar su estado (Boceto 8).
            Este boceto está destinado exclusivamente a usuarios con rol de cliente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto8_PedidoClient.png" alt="Boceto de pedidos del cliente">
        <figcaption>
            <p><strong>Descripción del Boceto 8:</strong> En esta pantalla el cliente puede consultar el historial de sus pedidos, así como el estado actual de los pedidos.</p>

            <p>Además, el cliente puede acceder al detalle de cada pedido para ver los productos solicitados y su evolución en el tiempo 
            (más detallado en el Boceto 14). Este boceto está pensado exclusivamente para usuarios con rol de cliente. </p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto9_PedidoCamarero.png" alt="Boceto de pedidos del camarero">
        <figcaption>
            <p><strong>Descripción del Boceto 9:</strong>  En esta pantalla el camarero puede visualizar los pedidos asignados y los pedidos pendientes de cobro o entrega.</p>

            <p>Desde esta vista, el camarero puede confirmar el pago de un pedido, cambiar su estado y gestionar la entrega al cliente de forma rápida y sencilla.
            Para acceder a esta pantalla, el camarero deberá pulsar el botón "Pedidos" en la barra de navegación superior.</p>

            <p>Este boceto está diseñado para ser utilizado por usuarios con rol de camarero, priorizando una interfaz clara y eficiente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto10_PedidoCocinero.png" alt="Boceto de pedidos del cocinero">
        <figcaption>
            <p><strong>Descripción del Boceto 10:</strong> En esta pantalla el cocinero puede consultar los pedidos que se encuentran en estado “En preparación”.</p>

            <p>El cocinero puede asignarse un pedido, cambiar su estado a “Cocinando”, marcar los productos preparados y finalizar la preparación del pedido cuando esté listo.
            Para acceder a esta pantalla, el cocinero deberá pulsar el botón "Pedidos" en la barra de navegación superior.</p>

            <p>Este boceto está pensado para usuarios con rol de cocinero y está optimizado para su uso en tablets.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto11_OfertasGerente.png" alt="Boceto de ofertas del gerente">
        <figcaption>
            <p><strong>Descripción del Boceto 11:</strong>  En esta pantalla el gerente puede gestionar las ofertas disponibles en la aplicación.</p>

            <p>Desde aquí, puede listar las ofertas activas y caducadas, crear nuevas ofertas, modificar las existentes o eliminarlas.
            Para acceder a esta pantalla, pulsará las tres lineas (más opciones) en la barra de navegación superior, en el apartado de "Ofertas".</p>

            <p>Este boceto solo puede ser utilizado por usuarios con rol de gerente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto12_OfertasCliente.png" alt="Boceto de ofertas del cliente">
        <figcaption>
            <p><strong>Descripción del Boceto 12:</strong>  En esta pantalla el cliente puede consultar las ofertas disponibles actualmente, visualizando su descripción, 
            los productos incluidos y el descuento aplicado.</p>

            <p>El cliente puede seleccionar una oferta y aplicarla a su pedido siempre que se cumplan las condiciones necesarias.
            Para acceder a esta pantalla, pulsará las tres lineas (más opciones) en la barra de navegación superior, en el apartado de "Ofertas".</p>

            <p>Este boceto está pensado para usuarios con rol de cliente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto13_RecompensasGerente.png" alt="Boceto de recompensas del gerente">
        <figcaption>
            <p><strong>Descripción del Boceto 13:</strong>  En esta pantalla el gerente puede gestionar el sistema de recompensas del programa de fidelización BistroCoins.
            El gerente puede crear, modificar, listar o eliminar recompensas, asignando productos de la carta y el número de BistroCoins necesarios para obtenerlos.</p>

            <p>Para acceder a esta pantalla, el gerente deberá pulsar el botón "Recompensas" en la barra de navegación superior. Este boceto solo puede ser utilizado por usuarios con rol de gerente. </p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto14_InfoPedido.png" alt="Boceto de info del pedido">
        <figcaption>
            <p><strong>Descripción del Boceto 14:</strong> En esta pantalla se muestra la información detallada de un pedido concreto, incluyendo los productos solicitados, las cantidades, 
            el estado actual, cocinero asignado, tipo (Local, Llevar), fecha y hora, cliente que realizó el pedido y el precio total.</p>

            <p>Para acceder a la información del pedido, el usuario deberá pulsar el botón "Pedidos" en la barra de navegación superior.</p>

            <p>Dependiendo del rol del usuario, se mostrarán distintas acciones y niveles de detalle.
            Este boceto puede ser utilizado por clientes, camareros, cocineros y gerentes.</p>
        </figcaption>
        </figure>

        
        <figure>
        <img src="img/Bocetos/Bcto15_ListaUsuarios.png" alt="Boceto de lista de usuarios">
        <figcaption>
            <p><strong>Descripción del Boceto 15:</strong>  En esta pantalla el gerente puede consultar el listado de usuarios registrados en la aplicación.
            Desde aquí, el gerente puede visualizar la información básica de cada usuario y modificar su rol dentro del sistema.</p>

            <p>Para acceder a esta pantalla, pulsará las tres lineas (más opciones) en la barra de navegación superior, en el apartado de "Usuarios".
            Este boceto solo puede ser utilizado por usuarios con rol de gerente.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto16_InfoRecompensa.png" alt="Boceto de info de recompensa">
        <figcaption>
            <p><strong>Descripción del Boceto 16:</strong> En esta pantalla se muestra la información detallada de una recompensa, incluyendo el producto asociado 
            y el número de BistroCoins necesarios para canjearla.</p>

            <p>Para acceder a la información del pedido, el usuario deberá pulsar el botón "Recompensas" en la barra de navegación superior.
            Dependiendo del rol del usuario, se permitirá visualizar o gestionar la recompensa, en este boceto el gerente puede modificar la información.</p>
        </figcaption>
        </figure>

        <figure>
        <img src="img/Bocetos/Bcto17_InfoOferta.png" alt="Boceto de info de oferta">
        <figcaption>
            <p><strong>Descripción del Boceto 17:</strong>  En esta pantalla se muestra la información detallada de una oferta, incluyendo los productos que la componen, 
            las cantidades requeridas, el descuento aplicado y el periodo de validez.</p>

            <p>Para acceder a esta pantalla, pulsará las tres lineas (más opciones) en la barra de navegación superior, en el apartado de "Ofertas".
            El único que tiene acceso a esta pantalla es el gerente, que puede modificar o eliminar la oferta desde esta vista.</p>
        </figcaption>
        </figure>
    </section>
