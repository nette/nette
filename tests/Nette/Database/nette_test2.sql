DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `nUsers`;
CREATE TABLE `nUsers` (
  `nUserId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`nUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `nUsers` (`nUserId`, `name`) VALUES
(1,	'John'),
(2,	'Doe');


DROP TABLE IF EXISTS `nTopics`;
CREATE TABLE `nTopics` (
  `nTopicId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`nTopicId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `nTopics` (`nTopicId`, `title`) VALUES
(10,	'Topic #1'),
(11,	'Topic #2'),
(12, 	'Topic #3');


DROP TABLE IF EXISTS `nUsers_nTopics`;
CREATE TABLE `nUsers_nTopics` (
	`nUserId` int(11) NOT NULL,
	`nTopicId` int(11) NOT NULL,
	PRIMARY KEY (`nUserId`, `nTopicId`),
	CONSTRAINT user_id FOREIGN KEY (nUserId) REFERENCES nUsers (nUserId),
	CONSTRAINT topic_id FOREIGN KEY (nTopicId) REFERENCES nTopics (nTopicId)
) ENGINE=InnoDB DEFAULT  CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `nUsers_nTopics` (`nUserId`, `nTopicId`) VALUES
(1, 10),
(1, 12),
(2, 11);

SET FOREIGN_KEY_CHECKS = 1;
