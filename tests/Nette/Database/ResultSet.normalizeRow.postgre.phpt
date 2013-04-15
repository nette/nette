<?php

/**
 * Test: Nette\Database\ResultSet::normalizeRow()
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini  postgresql
 */

$query = 'postgresql';
require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/files/pgsql-nette_test3.sql');



$res = $connection->query('SELECT * FROM types');

$row = $res->fetch();
Assert::true( is_string($row->money) );
unset($row->money);

Assert::equal( array(
	'smallint' => 1,
	'integer' => 1,
	'bigint' => 1,
	'numeric' => 1.0,
	'real' => 1.1,
	'double' => 1.11,
	'bool' => TRUE,
	'date' => new Nette\DateTime('2012-10-13'),
	'time' => new Nette\DateTime('10:10:10'),
	'timestamp' => new Nette\DateTime('2012-10-13 10:10:10'),
	'interval' => '1 year',
	'character' => 'a                             ',
	'character_varying' => 'a',
	'text' => 'a',
	'tsquery' => '\'a\'',
	'tsvector' => '\'a\'',
	'uuid' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
	'xml' => 'a',
	'cidr' => '192.168.1.0/24',
	'inet' => '192.168.1.1',
	'macaddr' => '08:00:2b:01:02:03',
	'bit' => '1',
	'bit_varying' => '1',
	'bytea' => NULL,
	'box' => '(30,40),(10,20)',
	'circle' => '<(10,20),30>',
	'lseg' => '[(10,20),(30,40)]',
	'path' => '((10,20),(30,40))',
	'point' => '(10,20)',
	'polygon' => '((10,20),(30,40))',
), (array) $row );

Assert::equal( array(
	'smallint' => 0,
	'integer' => 0,
	'bigint' => 0,
	'numeric' => 0.0,
	'real' => 0.0,
	'double' => 0.0,
	'money' => NULL,
	'bool' => FALSE,
	'date' => NULL,
	'time' => NULL,
	'timestamp' => NULL,
	'interval' => '00:00:00',
	'character' => '                              ',
	'character_varying' => '',
	'text' => '',
	'tsquery' => '',
	'tsvector' => '',
	'uuid' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
	'xml' => 'a',
	'cidr' => '192.168.1.0/24',
	'inet' => '192.168.1.1',
	'macaddr' => '08:00:2b:01:02:03',
	'bit' => '0',
	'bit_varying' => '0',
	'bytea' => NULL,
	'box' => '(30,40),(10,20)',
	'circle' => '<(10,20),30>',
	'lseg' => '[(10,20),(30,40)]',
	'path' => '((10,20),(30,40))',
	'point' => '(10,20)',
	'polygon' => '((10,20),(30,40))',
), (array) $res->fetch() );

Assert::equal( array(
	'smallint' => NULL,
	'integer' => NULL,
	'bigint' => NULL,
	'numeric' => NULL,
	'real' => NULL,
	'double' => NULL,
	'money' => NULL,
	'bool' => NULL,
	'date' => NULL,
	'time' => NULL,
	'timestamp' => NULL,
	'interval' => NULL,
	'character' => NULL,
	'character_varying' => NULL,
	'text' => NULL,
	'tsquery' => NULL,
	'tsvector' => NULL,
	'uuid' => NULL,
	'xml' => NULL,
	'cidr' => NULL,
	'inet' => NULL,
	'macaddr' => NULL,
	'bit' => NULL,
	'bit_varying' => NULL,
	'bytea' => NULL,
	'box' => NULL,
	'circle' => NULL,
	'lseg' => NULL,
	'path' => NULL,
	'point' => NULL,
	'polygon' => NULL,
), (array) $res->fetch() );


$res = $connection->query('SELECT "integer" AS a, "text" AS a FROM types');

Assert::equal( array(
	'a' => 'a',
), (array) @$res->fetch() );
