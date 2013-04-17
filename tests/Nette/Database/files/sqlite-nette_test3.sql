DROP TABLE IF EXISTS types;


CREATE TABLE types (
	[int] INT,
	[integer] INTEGER,
	[tinyint] TINYINT,
	[smallint] SMALLINT,
	[mediumint] MEDIUMINT,
	[bigint] BIGINT,
	[unsigned_big_int] UNSIGNED BIG INT,
	[int2] INT2,
	[int8] INT8,
	[character_20] CHARACTER(20),
	[varchar_255] VARCHAR(255),
	[varying_character_255] VARYING CHARACTER(255),
	[nchar_55] NCHAR(55),
	[native_character_70] NATIVE CHARACTER(70),
	[nvarchar_100] NVARCHAR(100),
	[text] TEXT,
	[clob] CLOB,
	[blob] BLOB,
	[real] REAL,
	[double] DOUBLE,
	[double precision] DOUBLE PRECISION,
	[float] FLOAT,
	[numeric] NUMERIC,
	[decimal_10_5] DECIMAL(10,5),
	[boolean] BOOLEAN,
	[date] DATE,
	[datetime] DATETIME
);


INSERT INTO types VALUES
(
	1, --int
	1, --integer
	1, --tinyint
	1, --smallint
	1, --mediumint
	1, --bigint
	1, --unsigned_big_int
	1, --int2
	1, --int8
	'a', --character_20
	'a', --varchar_255
	'a', --varying_character_255
	'a', --nchar_55
	'a', --native_character_70
	'a', --nvarchar_100
	'a', --text
	'a', --clob
	'a', --blob
	1.1, --real
	1.1, --double
	1.1, --double precision
	1.1, --float
	1.1, --numeric
	1.1, --decimal_10_5
	1, --boolean
	'2012-10-13', --date
	'2012-10-13 10:10:10' --datetime
);


INSERT INTO types VALUES (
	0, --int
	0, --integer
	0, --tinyint
	0, --smallint
	0, --mediumint
	0, --bigint
	0, --unsigned_big_int
	0, --int2
	0, --int8
	'', --character_20
	'', --varchar_255
	'', --varying_character_255
	'', --nchar_55
	'', --native_character_70
	'', --nvarchar_100
	'', --text
	'', --clob
	'', --blob
	0.5, --real
	0.5, --double
	0.5, --double precision
	0.5, --float
	0.5, --numeric
	0.5, --decimal_10_5
	0, --boolean
	'1970-01-01', --date
	'1970-01-01 00:00:00' --datetime
);


INSERT INTO types VALUES (
	NULL, --int
	NULL, --integer
	NULL, --tinyint
	NULL, --smallint
	NULL, --mediumint
	NULL, --bigint
	NULL, --unsigned_big_int
	NULL, --int2
	NULL, --int8
	NULL, --character_20
	NULL, --varchar_255
	NULL, --varying_character_255
	NULL, --nchar_55
	NULL, --native_character_70
	NULL, --nvarchar_100
	NULL, --text
	NULL, --clob
	NULL, --blob
	NULL, --real
	NULL, --double
	NULL, --double precision
	NULL, --float
	NULL, --numeric
	NULL, --decimal_10_5
	NULL, --boolean
	NULL, --date
	NULL --datetime
);
