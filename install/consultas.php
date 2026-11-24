<?php
$consultas = [];


// 1. Tabla users
$consultas[] = "CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `surname1` VARCHAR(50) NOT NULL,
  `surname2` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) NOT NULL,
  `foto_perfil` VARCHAR(255) DEFAULT NULL,
  `rol` VARCHAR(20) NOT NULL DEFAULT 'user',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_login` (`login`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


// 2. Tabla peliculas
$consultas[] = "CREATE TABLE IF NOT EXISTS `peliculas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(150) NOT NULL,
  `director` VARCHAR(100) NOT NULL,
  `anio` YEAR(4) NOT NULL,
  `genero` VARCHAR(50) NOT NULL,
  `poster` VARCHAR(255) DEFAULT NULL,
  `sinopsis` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


// 3. Tabla resenas
$consultas[] = "CREATE TABLE IF NOT EXISTS `resenas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `pelicula_id` INT(11) NOT NULL,
  `puntuacion` DECIMAL(3,1) NOT NULL,
  `comentario` TEXT DEFAULT NULL,
  `spoiler` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_vista` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_resenas_user` (`user_id`),
  KEY `idx_resenas_pelicula` (`pelicula_id`),
  CONSTRAINT `fk_resenas_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resenas_pelicula` FOREIGN KEY (`pelicula_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


// 4. Tabla watchlist
$consultas[] = "CREATE TABLE IF NOT EXISTS `watchlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `pelicula_id` INT(11) NOT NULL,
  `fecha_anadida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_watchlist_user` (`user_id`),
  KEY `idx_watchlist_pelicula` (`pelicula_id`),
  CONSTRAINT `fk_watchlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_watchlist_pelicula` FOREIGN KEY (`pelicula_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


// 5. Tabla favoritos
$consultas[] = "CREATE TABLE IF NOT EXISTS `favoritos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `pelicula_id` INT(11) NOT NULL,
  `fecha_anadida` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_favoritos_user` (`user_id`),
  KEY `idx_favoritos_pelicula` (`pelicula_id`),
  CONSTRAINT `fk_favoritos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favoritos_pelicula` FOREIGN KEY (`pelicula_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

?>