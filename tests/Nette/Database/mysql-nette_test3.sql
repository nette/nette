/*!40102 SET storage_engine = InnoDB */;

DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `types`;
CREATE TABLE `types` (
  `unsigned_int` int(11) unsigned,
  `int` int(11),
  `smallint` smallint(6),
  `tinyint` tinyint(4),
  `mediumint` mediumint(9),
  `bigint` bigint(20),
  `bit` bit(1),
  `decimal` decimal(10,0),
  `decimal2` decimal(10,2),
  `float` float,
  `double` double,
  `date` date,
  `time` time,
  `datetime` datetime,
  `timestamp` timestamp NULL,
  `year` year(4),
  `char` char(1),
  `varchar` varchar(30),
  `binary` binary(1),
  `varbinary` varbinary(30),
  `blob` blob,
  `tinyblob` tinyblob,
  `mediumblob` mediumblob,
  `longblob` longblob,
  `text` text,
  `tinytext` tinytext,
  `mediumtext` mediumtext,
  `longtext` longtext,
  `enum` enum('a','b'),
  `set` set('a','b')
) ENGINE=InnoDB;

INSERT INTO `types` (`unsigned_int`, `int`, `smallint`, `tinyint`, `mediumint`, `bigint`, `bit`, `decimal`, `decimal2`, `float`, `double`, `date`, `time`, `datetime`, `timestamp`, `year`, `char`, `varchar`, `binary`, `varbinary`, `blob`, `tinyblob`, `mediumblob`, `longblob`, `text`, `tinytext`, `mediumtext`, `longtext`, `enum`, `set`) VALUES
(1,	1,	1,	1,	1,	1,	1,	1,	1.1,	1,	1.1,	'2012-10-13',	'10:10:10',	'2012-10-13 10:10:10',	'2012-10-13 10:10:10',	'2012',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a',	'a'),
(0,	0,	0,	0,	0,	0,	0,	0,	0.5,	0.5,	0.5,	'0000-00-00',	'00:00:00',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'2000',	'',	'',	'\0',	'',	'',	'',	'',	'',	'',	'',	'',	'',	'b',	''),
(NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);
