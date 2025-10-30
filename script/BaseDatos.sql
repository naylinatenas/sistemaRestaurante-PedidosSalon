-- ==========================================
-- BASE DE DATOS: Sistema de Restaurante
-- Grupo 7 - Pedidos en Salón
-- Versión Completa y Corregida
-- ==========================================
DROP DATABASE IF EXISTS restauranteGrupo7;
CREATE DATABASE restauranteGrupo7;
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
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    INDEX idx_correo (correo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: mesa
-- ==========================================
CREATE TABLE mesa (
    id_mesa VARCHAR(10) PRIMARY KEY,
    numero_mesa INT NOT NULL UNIQUE,
    estado_mesa ENUM('libre', 'ocupada', 'limpiando') DEFAULT 'libre',
    INDEX idx_numero (numero_mesa),
    INDEX idx_estado (estado_mesa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: plato
-- ==========================================
CREATE TABLE plato (
    id_plato VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: pedido
-- ==========================================
CREATE TABLE pedido (
    id_pedido VARCHAR(10) PRIMARY KEY,
    mesa_id VARCHAR(10) NOT NULL,
    mozo_id VARCHAR(10) NOT NULL,
    hora_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    hora_cierre DATETIME NULL,
    total DECIMAL(10,2) DEFAULT 0.00,
    estado_pedido ENUM('abierto', 'cerrado') DEFAULT 'abierto',
    FOREIGN KEY (mesa_id) REFERENCES mesa(id_mesa) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (mozo_id) REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_mesa (mesa_id),
    INDEX idx_mozo (mozo_id),
    INDEX idx_estado (estado_pedido),
    INDEX idx_fecha (hora_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- TABLA: detalle_pedido
-- ==========================================
CREATE TABLE detalle_pedido (
    id_detalle VARCHAR(10) PRIMARY KEY,
    pedido_id VARCHAR(10) NOT NULL,
    plato_id VARCHAR(10) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (plato_id) REFERENCES plato(id_plato) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_plato (plato_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- DATOS DE PRUEBA: Usuarios
-- ==========================================
INSERT INTO usuario (id_usuario, nombre, correo, clave, rol, estado) VALUES
('U001', 'Admin', 'admin@restaurante.com', 'admin123', 'admin', 'activo'),
('U002', 'Alonso', 'Alonso@restaurante.com', 'mozo123', 'mozo', 'activo'),
('U003', 'Maria Torres', 'maria@restaurante.com', 'mozo123', 'mozo', 'activo');

-- ==========================================
-- DATOS DE PRUEBA: Mesas
-- ==========================================
INSERT INTO mesa (id_mesa, numero_mesa, estado_mesa) VALUES
('M001', 1, 'libre'),
('M002', 2, 'libre'),
('M003', 3, 'libre'),
('M004', 4, 'libre'),
('M005', 5, 'libre'),
('M006', 6, 'libre');

-- ==========================================
-- DATOS DE PRUEBA: Platos Completos
-- ==========================================
INSERT INTO plato (id_plato, nombre, categoria, precio, estado) VALUES
-- Entradas
('P001', 'Ensalada César', 'Entradas', 18.00, 'activo'),
('P002', 'Ceviche Clásico', 'Entradas', 28.00, 'activo'),
('P003', 'Tequeños', 'Entradas', 15.00, 'activo'),
('P004', 'Causa Limeña', 'Entradas', 20.00, 'activo'),

-- Platos Principales
('P005', 'Lomo Saltado', 'Platos Principales', 32.00, 'activo'),
('P006', 'Arroz con Pollo', 'Platos Principales', 25.00, 'activo'),
('P007', 'Ají de Gallina', 'Platos Principales', 28.00, 'activo'),
('P008', 'Tallarines Verdes', 'Platos Principales', 24.00, 'activo'),
('P009', 'Hamburguesa Clásica', 'Platos Principales', 22.00, 'activo'),
('P010', 'Pizza Margarita', 'Platos Principales', 30.00, 'activo'),
('P011', 'Pasta Carbonara', 'Platos Principales', 26.00, 'activo'),
('P012', 'Pollo a la Brasa (1/4)', 'Platos Principales', 18.00, 'activo'),

-- Bebidas
('P013', 'Inka Cola 500ml', 'Bebidas', 6.00, 'activo'),
('P014', 'Coca Cola 500ml', 'Bebidas', 6.00, 'activo'),
('P015', 'Limonada Natural', 'Bebidas', 8.00, 'activo'),
('P016', 'Chicha Morada', 'Bebidas', 7.00, 'activo'),
('P017', 'Cerveza Cristal', 'Bebidas', 10.00, 'activo'),
('P018', 'Agua Mineral', 'Bebidas', 4.00, 'activo'),

-- Postres
('P019', 'Helado de Vainilla', 'Postres', 12.00, 'activo'),
('P020', 'Tiramisú', 'Postres', 16.00, 'activo'),
('P021', 'Tres Leches', 'Postres', 14.00, 'activo'),
('P022', 'Suspiro Limeño', 'Postres', 13.00, 'activo');

-- ==========================================
-- VERIFICACIÓN
-- ==========================================
SELECT 'Base de datos creada exitosamente' AS mensaje;
SELECT COUNT(*) AS total_usuarios FROM usuario;
SELECT COUNT(*) AS total_mesas FROM mesa;
SELECT COUNT(*) AS total_platos_activos FROM plato WHERE estado = 'activo';