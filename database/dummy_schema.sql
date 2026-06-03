-- Base limpia para servidor dummy del Gestor de Expedientes Docentes.
-- Incluye estructura, catalogos basicos y un usuario administrador.
-- No incluye docentes reales, expedientes reales ni documentos cargados.
--
-- Usuario inicial:
--   usuario: admin
--   password: Admin1234
--
-- Recomendacion: cambiar esta contrasena al primer ingreso.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `document`;
DROP TABLE IF EXISTS `labor_academy`;
DROP TABLE IF EXISTS `record`;
DROP TABLE IF EXISTS `emergency_contact`;
DROP TABLE IF EXISTS `teaching`;
DROP TABLE IF EXISTS `labor_data`;
DROP TABLE IF EXISTS `document_type`;
DROP TABLE IF EXISTS `academy`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `role`;
DROP TABLE IF EXISTS `estatus_expediente`;

CREATE TABLE `academy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academy_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `document_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `estatus_expediente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_descripcion` (`descripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_rol` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `auth_key` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 10,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`),
  KEY `fk_user_role` (`role_id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `labor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `teaching` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `first_last_name` varchar(100) DEFAULT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `born_date` date DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_teaching_user` (`user_id`),
  CONSTRAINT `fk_teaching_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `emergency_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `number_phone` int(10) DEFAULT NULL,
  `parentesco` varchar(100) DEFAULT NULL,
  `teaching_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `teaching_id` (`teaching_id`),
  CONSTRAINT `emergency_contact_ibfk_1` FOREIGN KEY (`teaching_id`) REFERENCES `teaching` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teaching_id` int(11) DEFAULT NULL,
  `estatus_id` int(11) NOT NULL DEFAULT 1,
  `creation_date` datetime DEFAULT NULL,
  `labor_data_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_record_teaching` (`teaching_id`),
  KEY `fk_record_labor` (`labor_data_id`),
  KEY `fk_record_estatus` (`estatus_id`),
  CONSTRAINT `fk_record_estatus` FOREIGN KEY (`estatus_id`) REFERENCES `estatus_expediente` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_record_labor` FOREIGN KEY (`labor_data_id`) REFERENCES `labor_data` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_record_teaching` FOREIGN KEY (`teaching_id`) REFERENCES `teaching` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `labor_academy` (
  `labor_id` int(11) NOT NULL,
  `academy_id` int(11) NOT NULL,
  PRIMARY KEY (`labor_id`,`academy_id`),
  KEY `academy_id` (`academy_id`),
  CONSTRAINT `labor_academy_ibfk_1` FOREIGN KEY (`labor_id`) REFERENCES `labor_data` (`id`),
  CONSTRAINT `labor_academy_ibfk_2` FOREIGN KEY (`academy_id`) REFERENCES `academy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT NULL,
  `document_type_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `upload_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_document_record` (`record_id`),
  KEY `document_type_id` (`document_type_id`),
  CONSTRAINT `document_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_type` (`id`),
  CONSTRAINT `fk_document_record` FOREIGN KEY (`record_id`) REFERENCES `record` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role` (`id`, `name_rol`) VALUES
(1, 'Administrador'),
(2, 'Docente'),
(3, 'Consultor');

INSERT INTO `estatus_expediente` (`id`, `descripcion`) VALUES
(1, 'Registro'),
(2, 'Activo'),
(3, 'Inactivo');

INSERT INTO `academy` (`id`, `academy_name`) VALUES
(1, 'Ingenieria en Sistemas Computacionales'),
(2, 'Ingenieria en Administracion'),
(3, 'Ingenieria Industrial'),
(4, 'Ingenieria Ambiental'),
(5, 'Ingenieria Civil'),
(6, 'Ingenieria en Gestion Empresarial');

INSERT INTO `document_type` (`id`, `type_name`) VALUES
(1, 'C.V'),
(2, 'Cedula Licenciatura'),
(3, 'Cedula Maestria'),
(4, 'Cedula Doctorado'),
(5, 'Diplomado'),
(6, 'Certificado'),
(7, 'Capacitacion'),
(8, 'Documento Personal'),
(9, 'Cursos');

INSERT INTO `user` (
  `id`, `username`, `auth_key`, `password_hash`, `password_reset_token`,
  `email`, `status`, `created_at`, `updated_at`, `verification_token`,
  `role_id`, `must_change_password`
) VALUES (
  1,
  'admin',
  'BL2rJYOlpu-1kCq8e-J7hvaeeC7j3RgD',
  '$2y$13$EJ1So.WyzMifl890wj/HAO3LNNztvuQ0YRZmCAEEn8cr/o1H/FNIG',
  NULL,
  'admin@example.local',
  10,
  UNIX_TIMESTAMP(),
  UNIX_TIMESTAMP(),
  NULL,
  1,
  1
);

SET FOREIGN_KEY_CHECKS = 1;
