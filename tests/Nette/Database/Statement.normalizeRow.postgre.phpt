<?php

/**
 * Test: Nette\Database\Statement::normalizeRow()
 *
 * @author     David Grudl
 * @package    Nette\Database
 */

$_SERVER['argv'][1] = 'postgresql';
require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/pgsql-nette_test3.sql");



$res = $connection->query("SELECT * FROM types");

$row = $res->fetch();
Assert::true( is_string($row->float4) );
unset($row->float4);

Assert::equal( array(
	'int1' => 1,
	'int2' => 1,
	'int3' => 1,
	'float1' => 1.0,
	'float2' => 1.1,
	'float3' => 1.11,
	'bool' => TRUE,
	'date1' => new Nette\DateTime('2012-10-13'),
	'date2' => new Nette\DateTime('10:10:10'),
	'date3' => new Nette\DateTime('2012-10-13 10:10:10'),
	'date4' => '1 year',
	'str1' => 'a                             ',
	'str2' => 'a',
	'str3' => 'a',
	'str4' => '\'a\'',
	'str5' => '\'a\'',
	'str6' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
	'str7' => 'a',
	'str8' => '192.168.1.0/24',
	'str9' => '192.168.1.1',
	'str10' => '08:00:2b:01:02:03',
	'bin1' => '1',
	'bin2' => '1',
	'bin3' => NULL,
	'geo1' => '(30,40),(10,20)',
	'geo2' => '<(10,20),30>',
	'geo3' => '[(10,20),(30,40)]',
	'geo4' => '((10,20),(30,40))',
	'geo5' => '(10,20)',
	'geo6' => '((10,20),(30,40))',
), (array) $row );

Assert::equal( array(
	'int1' => 0,
	'int2' => 0,
	'int3' => 0,
	'float1' => 0.0,
	'float2' => '0',
	'float3' => '0',
	'float4' => NULL,
	'bool' => FALSE,
	'date1' => NULL,
	'date2' => NULL,
	'date3' => NULL,
	'date4' => '00:00:00',
	'str1' => '                              ',
	'str2' => '',
	'str3' => '',
	'str4' => '',
	'str5' => '',
	'str6' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
	'str7' => 'a',
	'str8' => '192.168.1.0/24',
	'str9' => '192.168.1.1',
	'str10' => '08:00:2b:01:02:03',
	'bin1' => '0',
	'bin2' => '0',
	'bin3' => NULL,
	'geo1' => '(30,40),(10,20)',
	'geo2' => '<(10,20),30>',
	'geo3' => '[(10,20),(30,40)]',
	'geo4' => '((10,20),(30,40))',
	'geo5' => '(10,20)',
	'geo6' => '((10,20),(30,40))',
), (array) $res->fetch() );

Assert::equal( array(
	'int1' => NULL,
	'int2' => NULL,
	'int3' => NULL,
	'float1' => NULL,
	'float2' => NULL,
	'float3' => NULL,
	'float4' => NULL,
	'bool' => NULL,
	'date1' => NULL,
	'date2' => NULL,
	'date3' => NULL,
	'date4' => NULL,
	'str1' => NULL,
	'str2' => NULL,
	'str3' => NULL,
	'str4' => NULL,
	'str5' => NULL,
	'str6' => NULL,
	'str7' => NULL,
	'str8' => NULL,
	'str9' => NULL,
	'str10' => NULL,
	'bin1' => NULL,
	'bin2' => NULL,
	'bin3' => NULL,
	'geo1' => NULL,
	'geo2' => NULL,
	'geo3' => NULL,
	'geo4' => NULL,
	'geo5' => NULL,
	'geo6' => NULL,
), (array) $res->fetch() );
