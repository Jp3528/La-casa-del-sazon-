CREATE DATABASE IF NOT EXISTS restaurante CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE restaurante;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ubicaciones_repartidores;
DROP TABLE IF EXISTS detalle_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS repartidores;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS metodos_pago;
DROP TABLE IF EXISTS clientes;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE clientes (
  id int(11) NOT NULL AUTO_INCREMENT,
  tipo_documento varchar(20) DEFAULT NULL,
  numero_documento varchar(30) DEFAULT NULL,
  nombre varchar(100) DEFAULT NULL,
  apellido varchar(100) DEFAULT NULL,
  telefono varchar(20) DEFAULT NULL,
  provincia varchar(50) DEFAULT NULL,
  ciudad varchar(50) DEFAULT NULL,
  direccion varchar(100) DEFAULT NULL,
  correo varchar(100) DEFAULT NULL,
  password varchar(255) DEFAULT NULL,
  fecha_registro timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE productos (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  descripcion text DEFAULT NULL,
  precio decimal(10,2) NOT NULL,
  stock int(11) NOT NULL DEFAULT 0,
  imagen varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE metodos_pago (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre_metodo varchar(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE repartidores (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(100) NOT NULL,
  telefono varchar(20) NOT NULL,
  vehiculo varchar(50) DEFAULT NULL,
  estado_disponibilidad enum('disponible','ocupado','inactivo') DEFAULT 'disponible',
  fecha_creacion timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY telefono (telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pedidos (
  id int(11) NOT NULL AUTO_INCREMENT,
  cliente_id int(11) NOT NULL,
  fecha timestamp NOT NULL DEFAULT current_timestamp(),
  estado enum('Pendiente','Enviado','Entregado','Cancelado') DEFAULT 'Pendiente',
  direccion_envio varchar(255) NOT NULL,
  total decimal(10,2) NOT NULL,
  metodo_pago_id int(11) NOT NULL,
  repartidor_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY cliente_id (cliente_id),
  KEY metodo_pago_id (metodo_pago_id),
  KEY repartidor_id (repartidor_id),
  CONSTRAINT pedidos_cliente_fk FOREIGN KEY (cliente_id) REFERENCES clientes (id),
  CONSTRAINT pedidos_pago_fk FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago (id),
  CONSTRAINT pedidos_repartidor_fk FOREIGN KEY (repartidor_id) REFERENCES repartidores (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE detalle_pedido (
  pedido_id int(11) NOT NULL,
  producto_id int(11) NOT NULL,
  cantidad int(11) DEFAULT NULL,
  PRIMARY KEY (pedido_id, producto_id),
  KEY producto_id (producto_id),
  CONSTRAINT detalle_pedido_pedido_fk FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
  CONSTRAINT detalle_pedido_producto_fk FOREIGN KEY (producto_id) REFERENCES productos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ubicaciones_repartidores (
  id int(11) NOT NULL AUTO_INCREMENT,
  repartidor_id int(11) NOT NULL,
  latitud decimal(10,8) NOT NULL,
  longitud decimal(11,8) NOT NULL,
  timestamp_ubicacion timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY repartidor_id (repartidor_id),
  CONSTRAINT ubicaciones_repartidor_fk FOREIGN KEY (repartidor_id) REFERENCES repartidores (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO clientes (id, tipo_documento, numero_documento, nombre, apellido, telefono, provincia, ciudad, direccion, correo, password) VALUES
(1, 'DNI', '00000000', 'Cliente', 'Prueba', '999999999', 'Lima', 'Lima', 'Direccion de prueba 123', 'cliente@test.com', '$2y$10$BXZmZX5M.XoY2A.kVmdeOeXWwxSEcdRwloRr7poPyDxTfzJrjw1OO');

INSERT INTO productos (id, nombre, descripcion, precio, stock, imagen) VALUES
(1, 'Arroz con pato', 'Arroz cocido en fondo de cilantro con tierno pato.', 24.00, 10, 'arrozconpato.webp'),
(2, 'Papa rellena', 'Tradicional platillo peruano elaborado con pure de papa y relleno sazonado.', 10.00, 8, 'platillo2.jpg'),
(3, 'Alfajores iquenos', 'Dulces tradicionales rellenos de manjar blanco y espolvoreados con azucar.', 10.00, 5, 'platillo3.jpg'),
(4, 'Hamburguesa Halloween Whopper', 'Hamburguesa especial con carne, queso y salsa de la casa.', 17.00, 6, 'platillo4.jpg'),
(5, 'Ceviche Mixto', 'Pescado y mariscos marinados con limon, cebolla y aji.', 30.00, 12, 'platillo5.jpg'),
(6, 'Encebollado de pollo', 'Pollo jugoso en salsa de cebolla con guarnicion.', 28.50, 9, 'platillo6.jpg');

INSERT INTO metodos_pago (id, nombre_metodo) VALUES
(1, 'Efectivo'),
(2, 'Yape'),
(3, 'Plin'),
(4, 'Tarjeta de debito'),
(5, 'Tarjeta de credito');

INSERT INTO repartidores (id, nombre, telefono, vehiculo, estado_disponibilidad) VALUES
(1, 'Juan Perez', '900000001', 'Moto', 'disponible'),
(2, 'Maria Gomez', '900000002', 'Moto', 'disponible');

INSERT INTO pedidos (id, cliente_id, estado, direccion_envio, total, metodo_pago_id, repartidor_id) VALUES
(1, 1, 'Enviado', 'Direccion de prueba 123', 24.00, 1, 1);

INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad) VALUES
(1, 1, 1);

INSERT INTO ubicaciones_repartidores (repartidor_id, latitud, longitud) VALUES
(1, -12.04637400, -77.04279300),
(2, -12.04271314, -77.03963780);
