DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `topics_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `topics` (`id`, `userId`, `title`) VALUES
(1,	2,	'Topic #1'),
(2,	2,	'Topic #2'),
(10,	1,	'Topic #3');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`id`, `name`) VALUES
(1,	'John'),
(2,	'Doe');

SET FOREIGN_KEY_CHECKS = 1;
