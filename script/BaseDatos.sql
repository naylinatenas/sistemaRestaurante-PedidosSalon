-- ==========================================
-- BASE DE DATOS: Sistema de Restaurante
-- Grupo 7 - Pedidos en Salón
-- Integrantes:
-- Acosta Plascencia, Naylin Atenas
-- Chuquipoma Medina, Sthefany Darley
-- Mantilla Sanchez, Elsa Lucia
-- ==========================================
CREATE DATABASE IF NOT EXISTS restauranteGrupo7;
USE restauranteGrupo7;

-- ==========================================
-- TABLA: usuario
-- ==========================================
CREATE TABLE usuario (
    id_usuario VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'mozo') NOT NULL DEFAULT 'mozo',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- ==========================================
-- TABLA: mesa
-- ==========================================
CREATE TABLE mesa (
    id_mesa VARCHAR(10) PRIMARY KEY,
    numero_mesa INT NOT NULL UNIQUE,
    estado_mesa ENUM('libre', 'ocupada', 'limpiando') DEFAULT 'libre'
);

-- ==========================================
-- TABLA: plato
-- ==========================================
CREATE TABLE plato (
    id_plato VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    precio DECIMAL(10,2) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- ==========================================
-- TABLA: pedido
-- ==========================================
CREATE TABLE pedido (
    id_pedido VARCHAR(10) PRIMARY KEY,
    mesa_id VARCHAR(10) NOT NULL,
    mozo_id VARCHAR(10) NOT NULL,
    hora_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    hora_cierre DATETIME NULL,
    total DECIMAL(10,2) DEFAULT 0,
    estado_pedido ENUM('abierto', 'cerrado') DEFAULT 'abierto',
    FOREIGN KEY (mesa_id) REFERENCES mesa(id_mesa),
    FOREIGN KEY (mozo_id) REFERENCES usuario(id_usuario)
);

-- ==========================================
-- TABLA: detalle_pedido
-- ==========================================
CREATE TABLE detalle_pedido (
    id_detalle VARCHAR(10) PRIMARY KEY,
    pedido_id VARCHAR(10) NOT NULL,
    plato_id VARCHAR(10) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id_pedido),
    FOREIGN KEY (plato_id) REFERENCES plato(id_plato)
);

-- ==========================================
-- TRIGGER: Calcular subtotal automáticamente
-- ==========================================
DELIMITER $$
CREATE TRIGGER calcular_subtotal_detalle
BEFORE INSERT ON detalle_pedido
FOR EACH ROW
BEGIN
    DECLARE precio_plato DECIMAL(10,2);
    SELECT precio INTO precio_plato FROM plato WHERE id_plato = NEW.plato_id;
    SET NEW.subtotal = NEW.cantidad * precio_plato;
END$$
DELIMITER ;

-- ==========================================
-- DATOS DE PRUEBA
-- ==========================================
INSERT INTO usuario (id_usuario, nombre, correo, clave, rol) VALUES
('U001', 'Admin', 'admin@restaurante.com', 'admin123', 'admin'),
('U002', 'Alonso', 'Alonso@restaurante.com', 'mozo123', 'mozo');

INSERT INTO mesa (id_mesa, numero_mesa) VALUES
('M001', 1),
('M002', 2),
('M003', 3),
('M004', 4);

INSERT INTO plato (id_plato, nombre, categoria, precio) VALUES
('P001', 'Lomo Saltado', 'Plato Fuerte', 25.50),
('P002', 'Ceviche', 'Entrada', 20.00),
('P003', 'Inka Cola 500ml', 'Bebida', 6.00);

INSERT INTO pedido (id_pedido, mesa_id, mozo_id) VALUES
('PED001', 'M001', 'U002');

INSERT INTO detalle_pedido (id_detalle, pedido_id, plato_id, cantidad) VALUES
('D001', 'PED001', 'P001', 2),
('D002', 'PED001', 'P003', 1);