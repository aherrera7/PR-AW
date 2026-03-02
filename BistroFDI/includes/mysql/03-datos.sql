-- 1. INSERTAR ROLES
INSERT INTO roles (nombre_rol) VALUES 
('cliente'),
('camarero'),
('cocinero'),
('gerente');

-- 2. INSERTAR USUARIOS
-- Nota: en un proyecto real la password debería ir hasheada, pero para datos de prueba vale
INSERT INTO usuarios (nombre_usuario, email, password, nombre, apellidos, avatar) VALUES 
('ana_gerente',   'ana@bistrofdi.es',   '1234', 'Ana',   'García',  'a1.png'),
('paco_chef',     'paco@bistrofdi.es',  '1234', 'Paco',  'Roncero', 'a2.png'),
('luis_camarero', 'luis@bistrofdi.es',  '1234', 'Luis',  'Sánchez', 'a3.png'),
('marta_cliente', 'marta@gmail.com',    '1234', 'Marta', 'López',   'default.jpg');

-- 3. ASIGNAR ROLES A USUARIOS
INSERT INTO roles_usuarios (id_usuario, id_rol) VALUES 
(1, 4), -- Ana es Gerente
(2, 3), -- Paco es Cocinero
(3, 2), -- Luis es Camarero
(4, 1); -- Marta es Cliente

-- 4. CATEGORÍAS
INSERT INTO categorias (nombre, descripcion, imagen) VALUES 
('Desayunos',    'Para empezar bien el día',            'desayuno.jpg'),
('Hamburguesas', 'Nuestras famosas burgers gourmet',    'hamburguesa.jpg'),
('Bebidas',      'Refrescos, cafés y más',              'bebida.jpg');

-- 5. PRODUCTOS (SIN imagen, porque ya no existe la columna imagen en productos)
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado) VALUES 
(1, 'Café con leche', 'Café arábica con leche fresca',                 1.25, 10, 1, 1),
(2, 'Burger FDI',     'Carne de buey, queso y salsa secreta',          8.50, 10, 1, 1),
(3, 'Agua Mineral',   'Botella de 50cl',                               1.50, 10, 1, 1);

-- 5.1 IMÁGENES DE PRODUCTOS (1 o más por producto)
-- Asumiendo que los productos insertados arriba quedan con IDs 1,2,3
INSERT INTO productos_imagenes (id_producto, ruta) VALUES
(1, 'cafe.jpg'),
(2, 'burger.jpg'),
(3, 'agua.jpg');

-- (Opcional) añadir más imágenes a un producto para cumplir "una o más"
INSERT INTO productos_imagenes (id_producto, ruta) VALUES (2, 'burger_detalle.jpg');

-- 6. PEDIDO DE PRUEBA
INSERT INTO pedidos (numero_pedido, id_cliente, estado, tipo, total) VALUES 
(1, 4, 'recibido', 'local', 11.00);

-- 7. DETALLE DEL PEDIDO
INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, precio_historico) VALUES 
(1, 1, 2, 1.25),
(1, 2, 1, 8.50);