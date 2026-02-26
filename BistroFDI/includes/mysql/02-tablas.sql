DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS pedidos_productos;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS roles_usuarios;

-- 1. Tabla de Roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE -- 'cliente', 'camarero', 'cocinero', 'gerente'
);

-- 2. Tabla de Usuarios (Modificada: quitamos el campo rol)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE, [cite: 106]
    email VARCHAR(100) NOT NULL, [cite: 107]
    password VARCHAR(255) NOT NULL, [cite: 110]
    nombre VARCHAR(100) NOT NULL, [cite: 108]
    apellidos VARCHAR(100) NOT NULL, [cite: 109]
    avatar VARCHAR(255) DEFAULT NULL [cite: 112]
);

-- 3. Tabla de Relación Rol-Usuario (Nueva)
-- Esta tabla cumple con lo que pidió el profesor para conectar ambos
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
    nombre VARCHAR(100) NOT NULL, [cite: 132]
    descripcion TEXT, [cite: 133]
    imagen VARCHAR(255) DEFAULT NULL [cite: 134]
);

-- 5. Tabla de Productos (Corregida la coma antes del FOREIGN KEY)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    nombre VARCHAR(100) NOT NULL, [cite: 137]
    descripcion TEXT, [cite: 138]
    precio_base DECIMAL(10, 2) NOT NULL, [cite: 142]
    iva INT NOT NULL, [cite: 143]
    disponible BOOLEAN DEFAULT TRUE, [cite: 144]
    ofertado BOOLEAN DEFAULT TRUE, [cite: 146, 150]
    imagen VARCHAR(255) DEFAULT NULL, [cite: 140]
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

-- 6. Tabla de Pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido INT NOT NULL, [cite: 179]
    id_cliente INT, [cite: 183]
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP, [cite: 181]
    estado ENUM('nuevo', 'recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado', 'entregado') DEFAULT 'nuevo', [cite: 161]
    tipo ENUM('local', 'llevar') NOT NULL, [cite: 173]
    total DECIMAL(10, 2) NOT NULL, [cite: 184]
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id)
);

-- 7. Detalle del Pedido
CREATE TABLE pedidos_productos (
    id_pedido INT,
    id_producto INT,
    cantidad INT NOT NULL, [cite: 177]
    precio_historico DECIMAL(10, 2) NOT NULL, 
    PRIMARY KEY (id_pedido, id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);