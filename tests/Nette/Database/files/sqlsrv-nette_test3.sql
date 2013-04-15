IF OBJECT_ID('types', 'U') IS NOT NULL DROP TABLE types;
IF OBJECT_ID('types2', 'U') IS NOT NULL DROP TABLE types2;



CREATE TABLE types (
	[bigint] bigint,
	[binary_3] binary(3),
	[bit] bit,
	[char_5] char(5),
	[date] date,
	[datetime] datetime,
	[datetime2] datetime2,
	[decimal] decimal,
	[float] float,
	[geography] geography,
	[geometry] geometry,
	[hierarchyid] hierarchyid,
	[int] int,
	[money] money,
	[nchar] nchar,
	[ntext] ntext,
	[numeric_10_0] numeric(10,0),
	[numeric_10_2] numeric(10,2),
	[nvarchar] nvarchar,
	[real] real,
	[smalldatetime] smalldatetime,
	[smallint] smallint,
	[smallmoney] smallmoney,
	[text] text,
	[time] time,
	[tinyint] tinyint,
	[uniqueidentifier] uniqueidentifier,
	[varbinary] varbinary,
	[varchar] varchar,
	[xml] xml
);

INSERT INTO types VALUES
(
	1, --bigint
	255, --binary
	1, --bit
	'a', --char
	'2012-10-13', --date
	'2012-10-13 10:10:10', --datetime
	'2012-10-13 10:10:10', --datetime2
	1, --decimal
	1.1, --float
	geography::STGeomFromText('LINESTRING(-122.360 47.656, -122.343 47.656 )', 4326), --geography
	geometry::STGeomFromText('LINESTRING (100 100, 20 180, 180 180)', 0), --geometry
	'/1/', --hierarchyid
	1, --int
	$1111.1, --money
	'a', --nchar
	'a', --ntext
	1, --numeric_10_0
	1.1, --numeric_10_2
	'a', --nvarchar
	1.1, --real
	'2012-10-13 10:10:10', --smalldatetime
	1, --smallint
	1.1, --smallmoney
	'a', --text
	'10:10:10', --time
	1, --tinyint
	'678e9994-a048-11e2-9030-003048d30c14', --uniqueidentifier
	1, --varbinary
	'a', --varchar
	'<doc/>' --xml
);

INSERT INTO types VALUES (
	0, --bigint
	0, --binary
	0, --bit
	'', --char
	'0001-01-01', --date
	'1753-01-01 00:00:00', --datetime
	'0001-01-01 00:00:00', --datetime2
	0, --decimal
	0.5, --float
	NULL, --geography
	NULL, --geometry
	'/', --hierarchyid
	'', --int
	'', --money
	'', --nchar
	'', --ntext
	0, --numeric_10_0
	0.5, --numeric_10_2
	'', --nvarchar
	'', --real
	'1900-01-01 00:00:00', --smalldatetime
	0, --smallint
	0.5, --smallmoney
	'', --text
	'00:00:00', --time
	0, --tinyint
	'00000000-0000-0000-0000-000000000000', --uniqueidentifier
	0, --varbinary
	'', --varchar
	'' --xml
);

INSERT INTO types VALUES (
	NULL, --bigint
	NULL, --binary
	NULL, --bit
	NULL, --char
	NULL, --date
	NULL, --datetime
	NULL, --datetime2
	NULL, --decimal
	NULL, --float
	NULL, --geography
	NULL, --geometry
	NULL, --hierarchyid
	NULL, --int
	NULL, --money
	NULL, --nchar
	NULL, --ntext
	NULL, --numeric_10_0
	NULL, --numeric_10_2
	NULL, --nvarchar
	NULL, --real
	NULL, --smalldatetime
	NULL, --smallint
	NULL, --smallmoney
	NULL, --text
	NULL, --time
	NULL, --tinyint
	NULL, --uniqueidentifier
	NULL, --varbinary
	NULL, --varchar
	NULL --xml
);



CREATE TABLE types2 (
	id int,
	[datetimeoffset] datetimeoffset,
	[sql_variant] sql_variant,
	[timestamp] timestamp
);

INSERT INTO types2 VALUES (
	1,
	'2012-10-13 10:10:10 +02:00', --datetimeoffset,
	cast(cast(123456 as int) as sql_variant), --sql_variant
	NULL --timestamp
);

INSERT INTO types2 VALUES (
	2,
	'0001-01-01 00:00:00 +00:00', --datetimeoffset,
	cast(cast('abcd' as varchar) as sql_variant), --sql_variant
	NULL --timestamp
);

INSERT INTO types2 VALUES (
	3,
	NULL, --datetimeoffset,
	NULL, --sql_variant
	NULL --timestamp
);
