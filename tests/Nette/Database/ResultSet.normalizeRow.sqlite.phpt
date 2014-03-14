<?php

/**
 * Test: Nette\Database\ResultSet::normalizeRow()
 *
 * @author     Miloslav Hůla
 * @dataProvider? databases.ini  sqlite
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/files/sqlite-nette_test3.sql');


$res = $context->query('SELECT * FROM types');

Assert::equal( array(
	'int' => 1,
	'integer' => 1,
	'tinyint' => 1,
	'smallint' => 1,
	'mediumint' => 1,
	'bigint' => 1,
	'unsigned_big_int' => 1,
	'int2' => 1,
	'int8' => 1,
	'character_20' => 'a',
	'varchar_255' => 'a',
	'varying_character_255' => 'a',
	'nchar_55' => 'a',
	'native_character_70' => 'a',
	'nvarchar_100' => 'a',
	'text' => 'a',
	'clob' => 'a',
	'blob' => 'a',
	'real' => 1.1,
	'double' => 1.1,
	'double precision' => 1.1,
	'float' => 1.1,
	'numeric' => 1.1,
	'decimal_10_5' => 1.1,
	'boolean' => TRUE,
	'date' => new Nette\DateTime('2012-10-13'),
	'datetime' => new Nette\DateTime('2012-10-13 10:10:10'),
), (array) $res->fetch() );

Assert::equal( array(
	'int' => 0,
	'integer' => 0,
	'tinyint' => 0,
	'smallint' => 0,
	'mediumint' => 0,
	'bigint' => 0,
	'unsigned_big_int' => 0,
	'int2' => 0,
	'int8' => 0,
	'character_20' => '',
	'varchar_255' => '',
	'varying_character_255' => '',
	'nchar_55' => '',
	'native_character_70' => '',
	'nvarchar_100' => '',
	'text' => '',
	'clob' => '',
	'blob' => '',
	'real' => 0.5,
	'double' => 0.5,
	'double precision' => 0.5,
	'float' => 0.5,
	'numeric' => 0.5,
	'decimal_10_5' => 0.5,
	'boolean' => FALSE,
	'date' => new Nette\DateTime('1970-01-01'),
	'datetime' => new Nette\DateTime('1970-01-01 00:00:00'),
), (array) $res->fetch() );

Assert::same( array(
	'int' => NULL,
	'integer' => NULL,
	'tinyint' => NULL,
	'smallint' => NULL,
	'mediumint' => NULL,
	'bigint' => NULL,
	'unsigned_big_int' => NULL,
	'int2' => NULL,
	'int8' => NULL,
	'character_20' => NULL,
	'varchar_255' => NULL,
	'varying_character_255' => NULL,
	'nchar_55' => NULL,
	'native_character_70' => NULL,
	'nvarchar_100' => NULL,
	'text' => NULL,
	'clob' => NULL,
	'blob' => NULL,
	'real' => NULL,
	'double' => NULL,
	'double precision' => NULL,
	'float' => NULL,
	'numeric' => NULL,
	'decimal_10_5' => NULL,
	'boolean' => NULL,
	'date' => NULL,
	'datetime' => NULL,
), (array) $res->fetch() );


$res = $context->query('SELECT [int] AS a, [text] AS a FROM types');

Assert::same( array(
	'a' => 'a',
), (array) @$res->fetch() );
