IF OBJECT_ID('note', 'U') IS NOT NULL DROP TABLE note;
IF OBJECT_ID('book_tag_alt', 'U') IS NOT NULL DROP TABLE book_tag_alt;
IF OBJECT_ID('book_tag', 'U') IS NOT NULL DROP TABLE book_tag;
IF OBJECT_ID('book', 'U') IS NOT NULL DROP TABLE book;
IF OBJECT_ID('tag', 'U') IS NOT NULL DROP TABLE tag;
IF OBJECT_ID('author', 'U') IS NOT NULL DROP TABLE author;



CREATE TABLE author (
	id int NOT NULL IDENTITY(11,1),
	name varchar(30) NOT NULL,
	web varchar(100) NOT NULL,
	born date,
	PRIMARY KEY(id)
);

INSERT INTO author (name, web, born) VALUES
('Jakub Vrana', 'http://www.vrana.cz/', NULL),
('David Grudl', 'http://davidgrudl.com/', NULL),
('Geek', 'http://example.com', NULL);



CREATE TABLE tag (
	id int NOT NULL IDENTITY(21, 1),
	name varchar(20) NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO tag (name) VALUES
('PHP'),
('MySQL'),
('JavaScript'),
('Neon');



CREATE TABLE book (
	id int NOT NULL IDENTITY(1,1),
	author_id int NOT NULL,
	translator_id int,
	title varchar(50) NOT NULL,
	next_volume int,
	PRIMARY KEY (id),
	CONSTRAINT book_author FOREIGN KEY (author_id) REFERENCES author (id),
	CONSTRAINT book_translator FOREIGN KEY (translator_id) REFERENCES author (id),
	CONSTRAINT book_volume FOREIGN KEY (next_volume) REFERENCES book (id)
);

CREATE INDEX book_title ON book (title);

INSERT INTO book (author_id, translator_id, title) VALUES
(11, 11, '1001 tipu a triku pro PHP'),
(11, NULL, 'JUSH'),
(12, 12, 'Nette'),
(12, 12, 'Dibi');



-- Add primary key manually, it is tested to name
CREATE TABLE book_tag (
	book_id int NOT NULL,
	tag_id int NOT NULL,
	CONSTRAINT book_tag_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
);
ALTER TABLE book_tag ADD CONSTRAINT PK_book_tag PRIMARY KEY CLUSTERED (book_id, tag_id);

INSERT INTO book_tag (book_id, tag_id) VALUES
(1, 21),
(3, 21),
(4, 21),
(1, 22),
(4, 22),
(2, 23);



CREATE TABLE book_tag_alt (
	book_id int NOT NULL,
	tag_id int NOT NULL,
	state varchar(30),
	PRIMARY KEY (book_id, tag_id),
	CONSTRAINT book_tag_alt_tag FOREIGN KEY (tag_id) REFERENCES tag (id),
	CONSTRAINT book_tag_alt_book FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE
);

INSERT INTO book_tag_alt (book_id, tag_id, state) VALUES
(3, 21, 'public'),
(3, 22, 'private'),
(3, 23, 'private'),
(3, 24, 'public');



CREATE TABLE note (
	book_id int NOT NULL,
	note varchar(100),
	CONSTRAINT note_book FOREIGN KEY (book_id) REFERENCES book (id)
);
