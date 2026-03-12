SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pedidos_productos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS productos_imagenes;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS roles_usuarios;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabla de Roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
);

-- 2. Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL
);

-- 3. Tabla de Relación Rol-Usuario
CREATE TABLE roles_usuarios (
    id_usuario INT,
    id_rol INT,
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE
);

-- 4. Tabla de Categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) DEFAULT NULL
);

-- 5. Tabla de Productos (sin campo imagen, porque ahora hay múltiples)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10, 2) NOT NULL,
    iva INT NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    ofertado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT
);

-- 5.1 Tabla de imágenes de productos (1 o más por producto)
CREATE TABLE productos_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- 6. Tabla de Pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido INT NOT NULL,
    id_cliente INT,
    id_camarero INT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('nuevo', 'recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado', 'entregado') DEFAULT 'nuevo',
    tipo ENUM('local', 'llevar') NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id),
    FOREIGN KEY (id_camarero) REFERENCES usuarios(id)
);

-- 7. Detalle del Pedido
CREATE TABLE pedidos_productos (
    id_pedido INT,
    id_producto INT,
    cantidad INT NOT NULL,
    precio_historico DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (id_pedido, id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);