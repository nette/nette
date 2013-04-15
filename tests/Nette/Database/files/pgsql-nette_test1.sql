DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;


CREATE TABLE author (
	id serial NOT NULL,
	name varchar(30) NOT NULL,
	web varchar(100) NOT NULL,
	born date DEFAULT NULL,
	PRIMARY KEY(id)
);

INSERT INTO author (id, name, web, born) VALUES (11, 'Jakub Vrana', 'http://www.vrana.cz/', NULL);
INSERT INTO author (id, name, web, born) VALUES (12, 'David Grudl', 'http://davidgrudl.com/', NULL);
INSERT INTO author (id, name, web, born) VALUES (13, 'Geek', 'http://example.com', NULL);
SELECT setval('author_id_seq', 13, TRUE);

CREATE TABLE tag (
	id serial NOT NULL,
	name varchar(20) NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO tag (id, name) VALUES (21, 'PHP');
INSERT INTO tag (id, name) VALUES (22, 'MySQL');
INSERT INTO tag (id, name) VALUES (23, 'JavaScript');
INSERT INTO tag (id, name) VALUES (24, 'Neon');
SELECT setval('tag_id_seq', 24, TRUE);

CREATE TABLE book (
	id serial NOT NULL,
	author_id int NOT NULL,
	translator_id int,
	title varchar(50) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT book_author FOREIGN KEY (author_id) REFERENCES author (id),
	CONSTRAINT book_translator FOREIGN KEY (translator_id) REFERENCES author (id)
);

CREATE INDEX book_title ON book (title);

INSERT INTO book (id, author_id, translator_id, title) VALUES (1, 11, 11, '1001 tipu a triku pro PHP');
INSERT INTO book (id, author_id, translator_id, title) VALUES (2, 11, NULL, 'JUSH');
INSERT INTO book (id, author_id, translator_id, title) VALUES (3, 12, 12, 'Nette');
INSERT INTO book (id, author_id, translator_id, title) VALUES (4, 12, 12, 'Dibi');
SELECT setval('book_id_seq', 4, TRUE);

CREATE TABLE book_tag (
	book_id int NOT NULL,
	tag_id int NOT NULL,
	PRIMARY KEY (book_id, tag_id),
	CONSTRAINT book_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
);

INSERT INTO book_tag (book_id, tag_id) VALUES (1, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (3, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (4, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (1, 22);
INSERT INTO book_tag (book_id, tag_id) VALUES (4, 22);
INSERT INTO book_tag (book_id, tag_id) VALUES (2, 23);

CREATE TABLE book_tag_alt (
	book_id int NOT NULL,
	tag_id int NOT NULL,
	state varchar(30),
	PRIMARY KEY (book_id, tag_id),
	CONSTRAINT book_tag_alt_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_alt_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
);

INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 21, 'public');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 22, 'private');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 23, 'private');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 24, 'public');
