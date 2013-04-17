DROP TABLE IF EXISTS book_tag_alt;
DROP TABLE IF EXISTS book_tag;
DROP TABLE IF EXISTS book;
DROP TABLE IF EXISTS tag;
DROP TABLE IF EXISTS author;




CREATE TABLE author (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	name TEXT NOT NULL,
	web TEXT NOT NULL,
	born DATE
);

INSERT INTO author (id, name, web, born) VALUES (11, 'Jakub Vrana', 'http://www.vrana.cz/', NULL);
INSERT INTO author (name, web, born) VALUES ('David Grudl', 'http://davidgrudl.com/', NULL);
INSERT INTO author (name, web, born) VALUES ('Geek', 'http://example.com', NULL);



CREATE TABLE tag (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	name TEXT NOT NULL
);

INSERT INTO tag (id, name) VALUES (21, 'PHP');
INSERT INTO tag (name) VALUES ('MySQL');
INSERT INTO tag (name) VALUES ('JavaScript');
INSERT INTO tag (name) VALUES ('Neon');



CREATE TABLE book (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	author_id INTEGER NOT NULL,
	translator_id INTEGER,
	title TEXT NOT NULL,
	next_volume INTEGER,
	CONSTRAINT book_author FOREIGN KEY (author_id) REFERENCES author (id),
	CONSTRAINT book_translator FOREIGN KEY (translator_id) REFERENCES author (id),
	CONSTRAINT book_volume FOREIGN KEY (next_volume) REFERENCES book (id)
);

CREATE INDEX book_title ON book (title);

INSERT INTO book (author_id, translator_id, title) VALUES (11, 11, '1001 tipu a triku pro PHP');
INSERT INTO book (author_id, translator_id, title) VALUES (11, NULL, 'JUSH');
INSERT INTO book (author_id, translator_id, title) VALUES (12, 12, 'Nette');
INSERT INTO book (author_id, translator_id, title) VALUES (12, 12, 'Dibi');



CREATE TABLE book_tag (
	book_id INTEGER NOT NULL,
	tag_id INTEGER NOT NULL,
	CONSTRAINT book_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE,
	PRIMARY KEY (book_id, tag_id)
);

INSERT INTO book_tag (book_id, tag_id) VALUES (1, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (3, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (4, 21);
INSERT INTO book_tag (book_id, tag_id) VALUES (1, 22);
INSERT INTO book_tag (book_id, tag_id) VALUES (4, 22);
INSERT INTO book_tag (book_id, tag_id) VALUES (2, 23);



CREATE TABLE book_tag_alt (
	book_id INTEGER NOT NULL,
	tag_id INTEGER NOT NULL,
	state TEXT,
	PRIMARY KEY (book_id, tag_id),
	CONSTRAINT book_tag_alt_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_alt_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
);

INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 21, 'public');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 22, 'private');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 23, 'private');
INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES (3, 24, 'public');
