-- Adminer 4.8.1 MySQL 10.7.3-MariaDB-1:10.7.3+maria~focal dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `hyphenated`;
CREATE TABLE `hyphenated` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `word` varchar(100) NOT NULL,
                              `hyphenated` varchar(100) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `hyphenated_patterns`;
CREATE TABLE `hyphenated_patterns` (
                                       `id` int(10) NOT NULL AUTO_INCREMENT,
                                       `hyphenated_id` int(11) NOT NULL,
                                       `pattern_id` int(10) NOT NULL,
                                       PRIMARY KEY (`id`),
                                       KEY `hyphenated_id` (`hyphenated_id`),
                                       KEY `pattern_id` (`pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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


-- 2022-04-18 18:33:17