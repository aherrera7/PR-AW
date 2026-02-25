-- 1. Tabla de Usuarios (Funcionalidad 0)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE, 
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    rol ENUM('cliente', 'camarero', 'cocinero', 'gerente') DEFAULT 'cliente', 
    avatar VARCHAR(255) DEFAULT NULL 
);

-- 2. Tabla de Categorías (Funcionalidad 1)
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) DEFAULT NULL 
);

-- 3. Tabla de Productos (Funcionalidad 1)
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10, 2) NOT NULL,
    iva INT NOT NULL, 
    disponible BOOLEAN DEFAULT TRUE, 
    ofertado BOOLEAN DEFAULT TRUE, 
    imagen VARCHAR(255) DEFAULT NULL;
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

-- 4. Tabla de Pedidos (Funcionalidad 2)
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido INT NOT NULL, -- Se incrementa cada día 
    id_cliente INT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP, 
    estado ENUM('nuevo', 'recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado', 'entregado') DEFAULT 'nuevo', 
    tipo ENUM('local', 'llevar') NOT NULL, 
    total DECIMAL(10, 2) NOT NULL, 
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id)
);

-- 5. Detalle del Pedido (Para los productos y cantidades)
CREATE TABLE pedidos_productos (
    id_pedido INT,
    id_producto INT,
    cantidad_solicitada INT NOT NULL, 
    precio_historico DECIMAL(10, 2) NOT NULL, -- Precio al que se vendió en ese momento
    PRIMARY KEY (id_pedido, id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);
