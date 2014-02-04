/*!40102 SET storage_engine = InnoDB */;

DROP DATABASE IF EXISTS nette_test;
CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

DELIMITER $$


CREATE TABLE author (
	id int NOT NULL AUTO_INCREMENT,
	name varchar(30) NOT NULL,
	web varchar(100) NOT NULL,
	born date DEFAULT NULL,
	PRIMARY KEY(id)
) AUTO_INCREMENT=13$$

delimiter ;
INSERT INTO author (id, name, web, born) VALUES (11, 'Jakub Vrana', 'http://www.vrana.cz/', NULL);
INSERT INTO author (id, name, web, born) VALUES (12, 'David Grudl', 'http://davidgrudl.com/', NULL)
