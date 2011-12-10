/*!40102 SET storage_engine = InnoDB */;

CREATE DATABASE IF NOT EXISTS nette_test;
USE nette_test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS author;
CREATE TABLE author (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(30) NOT NULL,
  web varchar(100) NOT NULL,
  born date DEFAULT NULL,
  PRIMARY KEY(id)
) AUTO_INCREMENT=13;

INSERT INTO author (id, name, web, born) VALUES (11, 'Jakub Vrana', 'http://www.vrana.cz/', NULL);
INSERT INTO author (id, name, web, born) VALUES (12, 'David Grudl', 'http://davidgrudl.com/', NULL);

DROP TABLE IF EXISTS tag;
CREATE TABLE tag (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL,
  PRIMARY KEY (id)
) AUTO_INCREMENT=24;

INSERT INTO tag (id, name) VALUES (21, 'PHP');
INSERT INTO tag (id, name) VALUES (22, 'MySQL');
INSERT INTO tag (id, name) VALUES (23, 'JavaScript');

DROP TABLE IF EXISTS application;
CREATE TABLE application (
  id int NOT NULL AUTO_INCREMENT,
  author_id int NOT NULL,
  maintainer_id int,
  title varchar(50) NOT NULL,
  web varchar(100) NOT NULL DEFAULT '',
  slogan varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  CONSTRAINT application_author FOREIGN KEY (author_id) REFERENCES author (id),
  CONSTRAINT application_maintainer FOREIGN KEY (maintainer_id) REFERENCES author (id)
) AUTO_INCREMENT=5;

CREATE INDEX application_title ON application (title);

INSERT INTO application (id, author_id, maintainer_id, title, web, slogan) VALUES (1, 11, 11, 'Adminer', 'http://www.adminer.org/', 'Database management in single PHP file');
INSERT INTO application (id, author_id, maintainer_id, title, web, slogan) VALUES (2, 11, NULL, 'JUSH', 'http://jush.sourceforge.net/', 'JavaScript Syntax Highlighter');
INSERT INTO application (id, author_id, maintainer_id, title, web, slogan) VALUES (3, 12, 12, 'Nette', 'http://nettephp.com/', 'Nette Framework for PHP 5');
INSERT INTO application (id, author_id, maintainer_id, title, web, slogan) VALUES (4, 12, 12, 'Dibi', 'http://dibiphp.com/', 'Database Abstraction Library for PHP 5');

DROP TABLE IF EXISTS application_tag;
CREATE TABLE application_tag (
  application_id int NOT NULL,
  tag_id int NOT NULL,
  PRIMARY KEY (application_id, tag_id),
  CONSTRAINT application_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
  CONSTRAINT application_tag_application FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE
);

INSERT INTO application_tag (application_id, tag_id) VALUES (1, 21);
INSERT INTO application_tag (application_id, tag_id) VALUES (3, 21);
INSERT INTO application_tag (application_id, tag_id) VALUES (4, 21);
INSERT INTO application_tag (application_id, tag_id) VALUES (1, 22);
INSERT INTO application_tag (application_id, tag_id) VALUES (4, 22);
INSERT INTO application_tag (application_id, tag_id) VALUES (2, 23);

SET FOREIGN_KEY_CHECKS = 1;
