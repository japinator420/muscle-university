/*
========================================= 
		ALUMNO: AMIR ALVAREZ
        MOTOR: MYSQL
=========================================
*/

DROP DATABASE IF EXISTS `muscle_university`;
CREATE DATABASE `muscle_university`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `muscle_university`;

/*
========================================= 
		USUARIOS Y SUSCRIPCIONES
=========================================
*/
-- Tabla central de usuarios (clientes, asesores y futuro admin)
CREATE TABLE `usuarios` (
    `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL,
    `correo` VARCHAR(100) NOT NULL UNIQUE,
    `rol` ENUM('cliente', 'asesor', 'admin') NOT NULL DEFAULT 'cliente',
    `edad` INT NULL,
    `inicio_contrato` DATE NULL,
    `estado` BOOLEAN NOT NULL DEFAULT TRUE,
    `fecha_registro` DATE NOT NULL DEFAULT (current_date),
    CONSTRAINT `chk_usuario_edad` CHECK (`edad` IS NULL OR `edad` BETWEEN 14 AND 120)
);

-- Suscripciones entre un usuario cliente y un usuario asesor
CREATE TABLE `suscripciones` (
    `id_suscripcion` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `id_asesor` INT NOT NULL,
    `estado` BOOLEAN NOT NULL DEFAULT TRUE,
    `inicio_suscripcion` DATE NOT NULL DEFAULT (current_date),
    `fin_suscripcion` DATE NULL,
    CONSTRAINT `fk_suscripciones_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `usuarios`(`id_usuario`),
    CONSTRAINT `fk_suscripciones_asesor`
        FOREIGN KEY (`id_asesor`) REFERENCES `usuarios`(`id_usuario`),
    CONSTRAINT `chk_suscripcion_fechas`
        CHECK (`fin_suscripcion` IS NULL OR `fin_suscripcion` >= `inicio_suscripcion`),
    CONSTRAINT `chk_suscripcion_distintos`
        CHECK (`id_cliente` <> `id_asesor`)
);

/*
========================================= 
		PRODUCTOS Y CATEGORIAS
=========================================
*/
CREATE TABLE `categorias` (
	`id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50),
    `descripcion` VARCHAR(100),
    `id_categoria_padre` INT NULL,
    `estado` BOOLEAN DEFAULT TRUE,
    `imagen_referencial` VARCHAR(255)
);

ALTER TABLE `categorias`
ADD CONSTRAINT `fk_catHijo_catPadre`
	FOREIGN KEY (`id_categoria_padre`)
    REFERENCES `categorias`(`id_categoria`);

CREATE TABLE `productos` (
	`id_producto` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50),
    `descripcion` VARCHAR(100),
    `precio` DECIMAL(10, 2) NOT NULL,
    `id_categoria` INT NOT NULL,
    `estado` BOOLEAN DEFAULT TRUE,
    `imagen_prod` VARCHAR (255),
    `fecha_ingreso` DATE DEFAULT (current_date)
);

ALTER TABLE `productos`
ADD CONSTRAINT `fk_producto_categoria`
	FOREIGN KEY (`id_categoria`)
    REFERENCES `categorias`(`id_categoria`);

/*
========================================= 
		ORDENES Y DETALLES
=========================================
*/
-- Las ordenes deben admitir un total tipo null para poder manejar el carrito de compra
CREATE TABLE `ordenes` (
    `id_orden` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `fecha_compra` DATE NOT NULL DEFAULT (current_date),
    `total` DECIMAL(10, 2) NULL,
    CONSTRAINT `fk_orden_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `usuarios`(`id_usuario`),
    CONSTRAINT `chk_orden_total`
        CHECK (`total` IS NULL OR `total` >= 0)
);

-- Para poder manejar la relacion muchos a muchos, necesitamos de esta tabla intermedia
CREATE TABLE `detalle_orden` (
    `id_detalle` INT AUTO_INCREMENT PRIMARY KEY,
    `id_orden` INT NOT NULL,
    `id_producto` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `precio_unitario` DECIMAL(10, 2) NOT NULL,
    `sub_total` DECIMAL(10, 2) NOT NULL,
    CONSTRAINT `fk_detalle_orden`
        FOREIGN KEY (`id_orden`) REFERENCES `ordenes`(`id_orden`),
    CONSTRAINT `fk_detalle_producto`
        FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id_producto`),
    CONSTRAINT `chk_detalle_cantidad`
        CHECK (`cantidad` > 0),
    CONSTRAINT `chk_detalle_precio`
        CHECK (`precio_unitario` >= 0),
    CONSTRAINT `chk_detalle_subtotal`
        CHECK (`sub_total` >= 0)
);

/*
========================================= 
		INSERTAMOS LOS USUARIOS
=========================================
*/
INSERT INTO `usuarios` (`nombre`, `correo`, `rol`, `edad`, `inicio_contrato`, `estado`) VALUES
('Peter Parker', 'peter.parker@muscleu.test', 'cliente', 22, NULL, TRUE),
('Diana Prince', 'diana.prince@muscleu.test', 'cliente', 28, NULL, TRUE),
('Chris Bumstead', 'cbum.coach@muscleu.test', 'asesor', 31, '2025-01-15', TRUE),
('Elena Rostova', 'elena.coach@muscleu.test', 'asesor', 29, '2025-06-01', TRUE),
('Admin General', 'admin@muscleu.test', 'admin', 35, '2024-01-10', TRUE);

/*
========================================= 
		INSERTAMOS LAS CATEGORIAS
=========================================
*/
INSERT INTO `categorias` (`nombre`, `descripcion`, `id_categoria_padre`, `estado`, `imagen_referencial`) VALUES
('Suplementos', 'Proteínas y vitaminas', NULL, TRUE, 'suplementos.jpg'),
('Proteínas', 'Suplementos en polvo', 1, TRUE, 'en_polvo.jpg'),
('Accesorios', 'Artículos de entrenamiento', NULL, TRUE, 'accesorios.jpg'),
('Ropa', 'Indumentaria deportiva', NULL, TRUE, 'ropa.jpg'),
('Creatina', 'Monohidrato de creatina', 1, TRUE, 'creatina.jpg');

/*
========================================= 
		INSERTAMOS LOS PRODUCTOS
=========================================
*/	
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `id_categoria`, `estado`, `imagen_prod`, `fecha_ingreso`) VALUES
-- Categoría 1: Suplementos
('Multivitamínico Daily', 'Fórmula diaria con vitaminas y minerales esenciales.', 59.90, 1, TRUE, 'multivitaminico.jpg', CURRENT_DATE),
('Omega 3 Ultra', 'Ácidos grasos EPA y DHA para salud articular y cardiovascular.', 74.90, 1, TRUE, 'omega3.jpg', CURRENT_DATE),
('Pre-Entreno Pump', 'Mezcla energética para enfoque y bombeo muscular.', 99.00, 1, TRUE, 'preentreno.jpg', CURRENT_DATE),
('ZMA Nocturno', 'Zinc, magnesio y vitamina B6 para recuperación.', 64.50, 1, TRUE, 'zma.jpg', CURRENT_DATE),
('Colágeno Hidrolizado', 'Soporte para tendones, ligamentos y articulaciones.', 79.00, 1, TRUE, 'colageno.jpg', CURRENT_DATE),

-- Categoría 2: Proteínas
('Whey Isolate 2lb', 'Proteína de suero aislada de rápida absorción.', 169.00, 2, TRUE, 'whey_isolate.jpg', CURRENT_DATE),
('Whey Concentrate 5lb', 'Proteína concentrada ideal para ganancia muscular.', 249.00, 2, TRUE, 'whey_concentrate.jpg', CURRENT_DATE),
('Caseína Micelar', 'Proteína de liberación lenta para consumo nocturno.', 179.00, 2, TRUE, 'caseina.jpg', CURRENT_DATE),
('Proteína Vegana Pea/Rice', 'Blend vegetal sin lácteos con perfil completo.', 159.00, 2, TRUE, 'proteina_vegana.jpg', CURRENT_DATE),
('Mass Gainer 6lb', 'Ganador de peso con carbohidratos complejos y proteína.', 219.00, 2, TRUE, 'mass_gainer.jpg', CURRENT_DATE),

-- Categoría 3: Accesorios
('Shaker Pro 700ml', 'Vaso mezclador antiderrame con rejilla metálica.', 29.90, 3, TRUE, 'shaker_700.jpg', CURRENT_DATE),
('Straps de Levantamiento', 'Bandas de algodón reforzado para peso muerto y remos.', 39.90, 3, TRUE, 'straps.jpg', CURRENT_DATE),
('Muñequeras Rígidas', 'Soporte firme para presses pesados y sentadillas.', 44.90, 3, TRUE, 'munequeras.jpg', CURRENT_DATE),
('Cinturón Lumbar Gym', 'Estabilización para levantamientos compuestos.', 119.00, 3, TRUE, 'cinturon.jpg', CURRENT_DATE),
('Calleras de Gimnasio', 'Protección palmar antideslizante para barras y dominadas.', 49.90, 3, TRUE, 'calleras.jpg', CURRENT_DATE),

-- Categoría 4: Ropa
('Polo Oversized Gym', 'Algodón pesado con caída amplia para entrenar.', 69.00, 4, TRUE, 'polo_oversized.jpg', CURRENT_DATE),
('Short Training 7"', 'Tela ligera y flexible con bolsillos con cierre.', 64.90, 4, TRUE, 'short_training.jpg', CURRENT_DATE),
('Jogger Fleece Atlético', 'Pantalón entallado cómodo para calentamiento o calle.', 94.90, 4, TRUE, 'jogger_fleece.jpg', CURRENT_DATE),
('Tank Top Stringer', 'Camiseta sin mangas de alto movimiento para torso.', 54.90, 4, TRUE, 'stringer.jpg', CURRENT_DATE),
('Hoodie Pump Cover', 'Sudadera amplia ideal para entrar en calor.', 114.90, 4, TRUE, 'hoodie_pump.jpg', CURRENT_DATE),

-- Categoría 5: Creatina
('Creatina Monohidrato 300g', 'Monohidrato micronizado para fuerza y potencia.', 89.00, 5, TRUE, 'creatina_300.jpg', CURRENT_DATE),
('Creatina Monohidrato 500g', 'Presentación rendidora sin sabor de fácil disolución.', 129.00, 5, TRUE, 'creatina_500.jpg', CURRENT_DATE),
('Creatina HCL Caps', 'Clorhidrato de creatina en cápsulas prácticas.', 95.00, 5, TRUE, 'creatina_hcl.jpg', CURRENT_DATE),
('Creatina Creapure 250g', 'Materia prima de alta pureza certificada.', 115.00, 5, TRUE, 'creapure.jpg', CURRENT_DATE),
('Creatina + Beta-Alanina', 'Combinación enfocada en resistencia y repeticiones.', 109.00, 5, TRUE, 'creatina_beta.jpg', CURRENT_DATE);

/*
========================================= 
		INSERTAMOS LAS SUSCRIPCIONES		
=========================================
*/
INSERT INTO `suscripciones` (`id_cliente`, `id_asesor`, `estado`, `inicio_suscripcion`, `fin_suscripcion`) VALUES
(1, 3, TRUE, '2026-10-01', NULL),
(2, 4, TRUE, '2026-11-01', '2027-02-01'),
(1, 4, FALSE, '2026-06-01', '2026-09-01'),
(2, 3, TRUE, '2026-09-15', '2026-12-15'),
(1, 3, FALSE, '2026-01-10', '2026-05-10');

/*
========================================= 
		INSERTAMOS LAS ORDENES
=========================================
*/
INSERT INTO `ordenes` (`id_cliente`, `fecha_compra`, `total`) VALUES
(1, '2026-11-10', 89.80),
(2, '2026-11-12', 243.90),
(1, '2026-11-15', 89.00),
(2, '2026-11-18', 168.00),
(1, '2026-11-21', 59.80);

/*
========================================= 
	INSERTAMOS LOS DETALLES DE ORDEN
=========================================
*/
INSERT INTO `detalle_orden` (`id_orden`, `id_producto`, `cantidad`, `precio_unitario`, `sub_total`) VALUES
-- Orden 1 (89.80): Multivitamínico Daily (59.90) + Shaker Pro 700ml (29.90)
(1, 1, 1, 59.90, 59.90),
(1, 11, 1, 29.90, 29.90),

-- Orden 2 (243.90): Whey Isolate 2lb (169.00) + Omega 3 Ultra (74.90)
(2, 6, 1, 169.00, 169.00),
(2, 2, 1, 74.90, 74.90),

-- Orden 3 (89.00): Creatina Monohidrato 300g (89.00)
(3, 21, 1, 89.00, 89.00),

-- Orden 4 (168.00): Polo Oversized Gym (69.00) + Pre-Entreno Pump (99.00)
(4, 16, 1, 69.00, 69.00),
(4, 3, 1, 99.00, 99.00),

-- Orden 5 (59.80): 2x Shaker Pro 700ml (29.90 c/u)
(5, 11, 2, 29.90, 59.80);


/*
========================================= 
	PROCEDIMIENTOS ALMACENADOS
=========================================
*/
DROP PROCEDURE IF EXISTS `sp_obtener_clientes_por_asesor`;

DELIMITER //

CREATE PROCEDURE `sp_obtener_clientes_por_asesor`(
    IN `p_id_asesor` INT
)
BEGIN
    SELECT
        u.`id_usuario` AS `id_cliente`,
        u.`nombre` AS `cliente`,
        u.`correo`,
        u.`edad`,
        s.`inicio_suscripcion`,
        s.`fin_suscripcion`,
        s.`estado`
    FROM `suscripciones` s
    INNER JOIN `usuarios` u ON s.`id_cliente` = u.`id_usuario`
    WHERE s.`id_asesor` = `p_id_asesor`
      AND s.`estado` = TRUE
      AND u.`rol` = 'cliente'
      AND u.`estado` = TRUE;
END //

DELIMITER ;

-- Los clientes del asesor con ID -> 3
CALL `sp_obtener_clientes_por_asesor`(3);