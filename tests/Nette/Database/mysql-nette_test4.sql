DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `nUsers`;
CREATE TABLE `nUsers` (
  `nUserId` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11),
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `version` int(11) NOT NULL,
  `status` char(1),
  `third` int(11),
  PRIMARY KEY (`nUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `nUsers` (`nUserId`, `users_id`, `name`, `version`, `status`, `third`) VALUES
(1, 1,	'John', 1, 'e', 0),
(2, 2,	'Doe', 1, 'e', 0),
(3, 1, 'Johnny', 2, 'u', 0),
(4, 2, 'Smith', 2, 'u', 0);

SET FOREIGN_KEY_CHECKS = 1;
