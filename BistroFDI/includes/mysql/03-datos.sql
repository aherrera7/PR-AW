
-- 1. Usuarios de prueba
INSERT INTO usuarios (nombre_usuario, email, password, nombre, apellidos, rol, avatar) VALUES
('gerente1',  'gerente@bistrofdi.es',  '1234', 'Ana',   'García',  'gerente',  'avatar_gerente.png'),
('cocinero1', 'chef@bistrofdi.es',     '1234', 'Paco',  'Roncero', 'cocinero', 'avatar_cocinero.png'),
('camarero1', 'camarero@bistrofdi.es', '1234', 'Luis',  'Sánchez', 'camarero', 'avatar_camarero.png'),
('cliente1',  'usuario@gmail.com',     '1234', 'Marta', 'López',   'cliente',  'avatar_defecto.png');

-- 2. Categorías
INSERT INTO categorias (nombre, descripcion, imagen) VALUES
('Bebidas',      'Refrescos, cafés y zumos',        'bebidas.jpg'),
('Hamburguesas', 'Nuestras famosas burgers gourmet', 'burgers.jpg'),
('Postres',      'Dulces para terminar bien',        'postres.jpg');

-- 3. Productos
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado, imagen) VALUES
(1, 'Café con leche', 'Café arábica con leche fresca', 1.25, 10, 1, 1, 'cafe.jpg'),
(2, 'Burger FDI',     'Carne de buey, queso y salsa secreta', 8.50, 10, 1, 1, 'burger_fdi.jpg'),
(3, 'Tarta de Queso', 'Receta casera de la abuela', 4.50, 10, 1, 1, 'tarta.jpg');

-- 4. Pedido (OJO: tras TRUNCATE el cliente1 normalmente será id=4 si lo insertas el 4º)
-- Si quieres que sea más robusto, usa subconsulta:
INSERT INTO pedidos (numero_pedido, id_cliente, estado, tipo, total) VALUES
(1, (SELECT id FROM usuarios WHERE nombre_usuario='cliente1'), 'recibido', 'local', 10.73);

-- 5. Detalle del pedido (también robusto con subconsultas)
INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad_solicitada, precio_historico) VALUES
((SELECT id FROM pedidos WHERE numero_pedido=1 ORDER BY id DESC LIMIT 1),
 (SELECT id FROM productos WHERE nombre='Café con leche'), 1, 1.25),
((SELECT id FROM pedidos WHERE numero_pedido=1 ORDER BY id DESC LIMIT 1),
 (SELECT id FROM productos WHERE nombre='Burger FDI'), 1, 8.50);