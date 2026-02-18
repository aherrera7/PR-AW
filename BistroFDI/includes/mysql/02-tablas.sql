USE BistroFDI;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS PedidoProductos;
DROP TABLE IF EXISTS Pedidos;
DROP TABLE IF EXISTS Productos;
DROP TABLE IF EXISTS Categorias;
DROP TABLE IF EXISTS RolesUsuario;
DROP TABLE IF EXISTS Roles;
DROP TABLE IF EXISTS Usuarios;

-- ROLES
CREATE TABLE Roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USUARIOS
CREATE TABLE Usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombreUsuario VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  apellidos VARCHAR(80),
  email VARCHAR(120),
  avatar VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RELACIÓN USUARIO–ROL
CREATE TABLE RolesUsuario (
  usuario INT NOT NULL,
  rol INT NOT NULL,
  PRIMARY KEY (usuario, rol),
  FOREIGN KEY (usuario) REFERENCES Usuarios(id),
  FOREIGN KEY (rol) REFERENCES Roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CATEGORÍAS
CREATE TABLE Categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  descripcion TEXT,
  imagen VARCHAR(255),
  activa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCTOS
CREATE TABLE Productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion TEXT,
  categoria INT NOT NULL,
  precioBase INT NOT NULL,              
  iva ENUM('4','10','21') NOT NULL,
  disponible TINYINT(1) NOT NULL DEFAULT 1,
  ofertado TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (categoria) REFERENCES Categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PEDIDOS
CREATE TABLE Pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente INT NOT NULL,
  fecha DATE NOT NULL,
  numero INT NOT NULL,
  tipo ENUM('local','llevar') NOT NULL,
  estado ENUM(
    'recibido','en_preparacion','cocinando',
    'listo_cocina','terminado','entregado'
  ) NOT NULL DEFAULT 'recibido',
  total INT NOT NULL DEFAULT 0,
  FOREIGN KEY (cliente) REFERENCES Usuarios(id),
  UNIQUE (fecha, numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PRODUCTOS DEL PEDIDO
CREATE TABLE PedidoProductos (
  pedido INT NOT NULL,
  producto INT NOT NULL,
  unidades INT NOT NULL,
  precioUnidad INT NOT NULL,            -- céntimos (congelado)
  iva ENUM('4','10','21') NOT NULL,
  PRIMARY KEY (pedido, producto),
  FOREIGN KEY (pedido) REFERENCES Pedidos(id),
  FOREIGN KEY (producto) REFERENCES Productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;