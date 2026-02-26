-- 1. INSERTAR ROLES (Según la jerarquía del enunciado: cliente < camarero < cocinero < gerente)
INSERT INTO roles (nombre_rol) VALUES 
('cliente'),    -- ID 1
('camarero'),   -- ID 2
('cocinero'),   -- ID 3
('gerente');    -- ID 4

-- 2. INSERTAR USUARIOS (Funcionalidad 0)
-- Nota: El enunciado pide nombre de usuario único, email, nombre, apellidos y password[cite: 105, 107, 108, 109, 110].
INSERT INTO usuarios (nombre_usuario, email, password, nombre, apellidos, avatar) VALUES 
('ana_gerente', 'ana@bistrofdi.es', '1234', 'Ana', 'García', 'avatar1.png'),
('paco_chef', 'paco@bistrofdi.es', '1234', 'Paco', 'Roncero', 'avatar2.png'),
('luis_camarero', 'luis@bistrofdi.es', '1234', 'Luis', 'Sánchez', 'avatar3.png'),
('marta_cliente', 'marta@gmail.com', '1234', 'Marta', 'López', 'default.png');

-- 3. ASIGNAR ROLES A USUARIOS (Relación muchos a muchos solicitada por el profesor)
INSERT INTO roles_usuarios (id_usuario, id_rol) VALUES 
(1, 4), -- Ana es Gerente
(2, 3), -- Paco es Cocinero
(3, 2), -- Luis es Camarero
(4, 1); -- Marta es Cliente [cite: 124]

-- 4. CATEGORÍAS (Funcionalidad 1)
-- El gerente gestiona categorías con nombre, descripción e imagen opcional[cite: 131, 132, 133, 134].
INSERT INTO categorias (nombre, descripcion, imagen) VALUES 
('Desayunos', 'Para empezar bien el día', 'desayuno.jpg'),
('Hamburguesas', 'Nuestras famosas burgers gourmet', 'hamburguesa.jpg'),
('Bebidas', 'Refrescos, cafés y más', 'bebida.jpg');

-- 5. PRODUCTOS (Funcionalidad 1)
-- Incluye nombre, descripción, precio base, IVA (4, 10, 21) y estado[cite: 137, 138, 142, 143, 144].
INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, disponible, ofertado, imagen) VALUES 
(1, 'Café con leche', 'Café arábica con leche fresca', 1.25, 10, 1, 1, 'cafe.jpg'),
(2, 'Burger FDI', 'Carne de buey, queso y salsa secreta', 8.50, 10, 1, 1, 'burger.jpg'),
(3, 'Agua Mineral', 'Botella de 50cl', 1.50, 10, 1, 1, 'agua.jpg');

-- 6. PEDIDO DE PRUEBA (Funcionalidad 2)
-- Un pedido para marta_cliente (ID 4) en estado 'recibido'[cite: 163, 183].
-- El número de pedido se reinicia cada día (ej. Pedido nº 1 del día)[cite: 195, 205].
INSERT INTO pedidos (numero_pedido, id_cliente, estado, tipo, total) VALUES 
(1, 4, 'recibido', 'local', 11.00);

-- 7. DETALLE DEL PEDIDO (Productos dentro del pedido)
-- Marta pidió 2 cafés y 1 burger.
INSERT INTO pedidos_productos (id_pedido, id_producto, cantidad, precio_historico) VALUES 
(1, 1, 2, 1.25), -- 2 Cafés
(1, 2, 1, 8.50); -- 1 Burger