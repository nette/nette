/*!40102 SET storage_engine = InnoDB */;

DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `types`;
CREATE TABLE `types` (
  `int1` int(11),
  `int2` int(11),
  `int3` smallint(6),
  `int4` tinyint(4),
  `int5` mediumint(9),
  `int6` bigint(20),
  `int7` bit(1),
  `float1` decimal(10,2),
  `float2` decimal(10,2),
  `float3` float,
  `float4` double,
  `date1` date,
  `date2` time,
  `date3` datetime,
  `date4` timestamp NULL,
  `date5` year(4),
  `str1` char(1),
  `str2` varchar(30),
  `str3` binary(1),
  `str4` varbinary(30),
  `str5` blob,
  `str6` text,
  `str7` enum('a','b'),
  `str8` set('a','b')
) ENGINE=InnoDB;

INSERT INTO `types` (`int1`, `int2`, `int3`, `int4`, `int5`, `int6`, `int7`, `float1`, `float2`, `float3`, `float4`, `date1`, `date2`, `date3`, `date4`, `date5`, `str1`, `str2`, `str3`, `str4`, `str5`, `str6`, `str7`, `str8`) VALUES
(1,	1,	1,	1,	1,	1,	1,	1.00,	1.10,	1,	1,	'2012-10-13',	'10:10:10',	'2012-10-13 10:10:10',	'2012-10-13 10:10:10',	'2012',	'a',	'a',	'a',	'a',	NULL,	'a',	'a',	'a'),
(0,	0,	0,	0,	0,	0,	0,	0.50,	0.50,	0.5,	0.5,	'0000-00-00',	'00:00:00',	'0000-00-00 00:00:00',	'0000-00-00 00:00:00',	'2000',	'',	'',	NULL,	'',	NULL,	'',	'b',	''),
(NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);
