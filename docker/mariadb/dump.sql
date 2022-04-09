-- Adminer 4.8.1 MySQL 10.7.3-MariaDB-1:10.7.3+maria~focal dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `patterns`;
CREATE TABLE `patterns` (
                            `id` int(10) NOT NULL AUTO_INCREMENT,
                            `value` varchar(100) NOT NULL,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `words`;
CREATE TABLE `words` (
                         `id` int(10) NOT NULL AUTO_INCREMENT,
                         `value` varchar(100) NOT NULL,
                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2022-04-09 16:26:22