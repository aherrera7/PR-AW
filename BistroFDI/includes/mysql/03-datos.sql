USE BistroFDI;

SET FOREIGN_KEY_CHECKS=0;

TRUNCATE TABLE PedidoProductos;
TRUNCATE TABLE Pedidos;
TRUNCATE TABLE Productos;
TRUNCATE TABLE Categorias;
TRUNCATE TABLE Usuarios;


-- USUARIOS
-- Contraseñas:
-- cliente123, camarero123, cocinero123, gerente123
INSERT INTO Usuarios (id, nombreUsuario, nombre, password) VALUES
(1,'cliente','Cliente','$2y$10$HASH_CLIENTE'),
(2,'camarero','Camarero','$2y$10$HASH_CAMARERO'),
(3,'cocinero','Cocinero','$2y$10$HASH_COCINERO'),
(4,'gerente','Gerente','$2y$10$HASH_GERENTE');


-- CATEGORÍAS
INSERT INTO Categorias (id,nombre,descripcion) VALUES
(1,'Bebidas','Bebidas frías y calientes'),
(2,'Desayunos','Desayunos y tostadas');

-- PRODUCTOS
INSERT INTO Productos (id,nombre,descripcion,categoria,precioBase,iva) VALUES
(1,'Café con leche','Café con leche',1,125,10),
(2,'Té','Té caliente',1,110,10),
(3,'Tostada con tomate','Tostada de tomate y aceite',2,130,10);

SET FOREIGN_KEY_CHECKS=1;
