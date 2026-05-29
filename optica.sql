-- 1. TABLA DE PREVISIONES (Mantiene tu lógica chilena)
CREATE TABLE `prevision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prevision` (`id`, `nombre`) VALUES
(1, 'Particular'),
(2, 'Fonasa'),
(3, 'Isapre');

-- 2. TABLA DE USUARIOS (Corregida la clave primaria y longitud del RUT)
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) NOT NULL UNIQUE, -- Ej: 12.345.678-K
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL, -- Espacio para hash seguro (Bcrypt)
  `id_prevision` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_prevision`) REFERENCES `prevision` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. TABLA DE PROFESIONALES (Médicos/Optometristas)
CREATE TABLE `profesionales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) NOT NULL UNIQUE,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. TABLA DE HORAS DISPONIBLES
CREATE TABLE `horas_disponibles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_profesional` int(11) NOT NULL,
  `estado` enum('disponible', 'no_disponible') NOT NULL DEFAULT 'disponible',
  PRIMARY KEY (`id`),
  KEY `idx_fecha_hora` (`fecha`, `hora`),
  FOREIGN KEY (`id_profesional`) REFERENCES `profesionales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. TABLA DE CITAS (Eliminada la redundancia de id_profesional)
CREATE TABLE `citas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_hora_disponible` int(11) NOT NULL UNIQUE, -- Una hora solo puede tener una cita activa
  `id_usuario` int(11) NOT NULL,
  `estado` enum('activo', 'cancelado', 'completado') NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_hora_disponible`) REFERENCES `horas_disponibles` (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. TABLA DE PRODUCTOS (Actualizada con campos para el diseño HTML)
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` int(11) NOT NULL,             -- Precio actual en CLP
  `precio_anterior` int(11) DEFAULT NULL,    -- Para mostrar descuentos en el HTML
  `imagen_url` varchar(255) DEFAULT NULL,     -- Ruta o URL de la foto del lente
  `etiqueta` varchar(30) DEFAULT NULL,       -- Ej: 'Nuevo', 'Oferta'
  `stock` int(11) NOT NULL DEFAULT 0,        -- Control de inventario para la tienda
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert de prueba con datos adaptados a tu HTML y DB original
INSERT INTO `productos` (`titulo`, `marca`, `descripcion`, `precio`, `precio_anterior`, `imagen_url`, `etiqueta`, `stock`) VALUES
('Classic Black', 'Ray-Ban', 'Armazón elegante de acetato negro con acabado premium.', 120000, 150000, 'url_imagen_1.jpg', 'Nuevo', 15),
('Aviator Gold', 'Ray-Ban', 'Gafas de sol estilo aviador con marco dorado.', 144000, NULL, 'url_imagen_2.jpg', 'Nuevo', 8);

-- 7. NUEVA: TABLA DE PEDIDOS (Para procesar las compras del Carrito)
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` int(11) NOT NULL,
  `estado` enum('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. NUEVA: TABLA DETALLE DE PEDIDOS (Relación Muchos a Muchos entre Pedidos y Productos)
CREATE TABLE `pedido_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` int(11) NOT NULL, -- Se guarda el precio del momento de la compra
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;