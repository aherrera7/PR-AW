SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM pedidos_productos;
DELETE FROM pedidos;
DELETE FROM productos_imagenes;
DELETE FROM productos;
DELETE FROM ofertas_productos;
DELETE FROM ofertas;
DELETE FROM categorias;
DELETE FROM roles_usuarios;
DELETE FROM roles;
DELETE FROM usuarios;

SET FOREIGN_KEY_CHECKS = 1;
-- 1. ROLES
INSERT INTO roles (id_rol, nombre_rol) VALUES
(1, 'cliente'),
(2, 'camarero'),
(3, 'cocinero'),
(4, 'gerente');

-- 2. USUARIOS
INSERT INTO usuarios (id, nombre_usuario, email, password, nombre, apellidos, avatar) VALUES
(1, 'ana_gerente',    'ana@bistrofdi.es',    '1234', 'Ana',   'García',   'avatares/a1.png'),
(2, 'paco_chef',      'paco@bistrofdi.es',   '1234', 'Paco',  'Roncero',  'avatares/a2.png'),
(3, 'luis_camarero',  'luis@bistrofdi.es',   '1234', 'Luis',  'Sánchez',  'avatares/a3.png'),
(4, 'marta_cliente',  'marta@gmail.com',     '1234', 'Marta', 'López',    'avatares/default.jpg'),
(5, 'carlos_cliente', 'carlos@gmail.com',    '1234', 'Carlos','Pérez',    'avatares/default.jpg');

-- 3. ROLES-USUARIOS
INSERT INTO roles_usuarios (id_usuario, id_rol) VALUES
(1, 4), -- gerente
(2, 3), -- cocinero
(3, 2), -- camarero
(4, 1), -- cliente
(5, 1); -- cliente

-- 4. CATEGORÍAS
INSERT INTO categorias (id, nombre, descripcion, imagen) VALUES
(1, 'Desayunos', 'Para empezar bien el día', 'categorias/desayuno.png'),
(2, 'Comida', 'Platos principales del Bistro FDI', 'categorias/comida.jpg'),
(3, 'Bebidas', 'Refrescos y bebidas', 'categorias/bebida.png');

-- 5. PRODUCTOS
INSERT INTO productos (id, id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado, es_cocina) VALUES
(1, 2, 'Nachos', 'Nachos con queso y guacamole', 6.50, 10, 1, 1, 1),
(2, 1, 'Tostada de tomate', 'Pan tostado con aceite y tomate', 1.30, 10, 1, 1, 1),
(3, 3, 'Agua Mineral', 'Botella de 50cl', 1.50, 10, 1, 1, 0),
(4, 3, 'Coca-Cola', 'Refresco de cola 33cl', 2.00, 10, 1, 1, 0),
(5, 3, 'Café', 'Café solo', 1.20, 10, 1, 1, 0),
(6, 2, 'Hamburguesa', 'Hamburguesa completa', 8.50, 10, 1, 1, 1),
(7, 2, 'Patatas fritas', 'Ración de patatas fritas', 3.00, 10, 1, 1, 1);

INSERT INTO productos_imagenes (id_producto, ruta) VALUES
(1, 'productos/nachos.png'),
(2, 'productos/tostada.png'),
(3, 'productos/agua.png'),
(4, 'productos/cocacola.png'),
(5, 'productos/cafe.png'),
(6, 'productos/hamburguesa.png'),
(7, 'productos/patatas.png');

-- OFERTAS
INSERT INTO ofertas (id, nombre, descripcion, fecha_inicio, fecha_fin, descuento, activa) VALUES
(1, 'Desayuno Andaluz', 'Incluye 1 café y 1 tostada de tomate', '2026-03-01', '2026-12-31', 0.2157, 1),
(2, 'Pack Refresco', 'Incluye 2 Coca-Cola con descuento', '2026-03-01', '2026-06-30', 0.1500, 1),
(3, 'Combo Burger', 'Incluye 1 hamburguesa, 1 Coca-Cola y 1 ración de patatas fritas', '2026-03-01', '2026-12-31', 0.2000, 1);

INSERT INTO ofertas_productos (id_oferta, id_producto, cantidad) VALUES
(1, 5, 1),
(1, 2, 1),
(2, 4, 2),
(3, 6, 1),
(3, 4, 1),
(3, 7, 1);

-- Pedido 1: RECIBIDO (para cobrar camarero)
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(1, 1, 4, NULL, '2026-03-13 09:30:00', 'recibido', 'local', 10.00);

-- Pedido 2: EN PREPARACIÓN
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(2, 2, 4, NULL, '2026-03-13 09:40:00', 'en preparación', 'llevar', 11.80);

-- Pedido 3: COCINANDO
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(3, 3, 5, 2, '2026-03-13 10:00:00', 'cocinando', 'local', 12.00);

-- Pedido 4: LISTO COCINA
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(4, 4, 4, 2, '2026-03-13 10:10:00', 'listo cocina', 'llevar', 9.80);

-- Pedido 5: TERMINADO
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(5, 5, 5, 2, '2026-03-13 10:20:00', 'terminado', 'local', 3.30);

-- Pedido 6: ENTREGADO
INSERT INTO pedidos (id, numero_pedido, id_cliente, id_cocinero, fecha_hora, estado, tipo, total) VALUES
(6, 6, 4, 2, '2026-03-12 13:15:00', 'entregado', 'local', 10.50);

-- Pedido 1
INSERT INTO pedidos_productos VALUES
(1, 1, 1, 6.50),
(1, 3, 1, 1.50);

-- Pedido 2
INSERT INTO pedidos_productos VALUES
(2, 1, 1, 6.50),
(2, 4, 1, 2.00),
(2, 3, 1, 1.50);

-- Pedido 3
INSERT INTO pedidos_productos VALUES
(3, 1, 1, 6.50),
(3, 4, 1, 2.00),
(3, 3, 1, 1.50);

-- Pedido 4
INSERT INTO pedidos_productos VALUES
(4, 1, 1, 6.50),
(4, 3, 1, 1.50);

-- Pedido 5
INSERT INTO pedidos_productos VALUES
(5, 2, 1, 1.30),
(5, 4, 1, 2.00);

-- Pedido 6
INSERT INTO pedidos_productos VALUES
(6, 1, 1, 6.50),
(6, 4, 1, 2.00);