
-- Doctor Directory - Database Schema v1.0.0



CREATE TABLE `wp_dd_doctors` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`  VARCHAR(150) NOT NULL,
  `email`      VARCHAR(255) NOT NULL,
  `address`    TEXT NOT NULL,
  `status`     TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dd_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Sample data


INSERT INTO `wp_dd_doctors` (`full_name`, `email`, `address`, `status`) VALUES
('Dr. Carlos Méndez Reyes',   'carlos.mendez@clinicasanar.com.do',  'Av. Winston Churchill 1099, Piantini, Santo Domingo',    1),
('Dra. María Fernanda López', 'mflopez@hospitalprivado.com.do',     'Calle El Conde 45, Zona Colonial, Santo Domingo',        1),
('Dr. Roberto Almonte Cruz',  'ralmonte@centromedico.com.do',       'Av. John F. Kennedy Km 6.5, Los Prados, Santo Domingo',  1),
('Dra. Luisa Estévez Vargas', 'lestevez@clinicabelen.com.do',       'Av. Independencia 701, Gazcue, Santo Domingo',           1),
('Dr. Antonio Jiménez Peña',  'ajimenez@medicaribe.com.do',         'Calle Duarte 34, Santiago de los Caballeros',            1);


