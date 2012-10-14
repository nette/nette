<?php

/**
 * Test: Nette\Database\Statement::normalizeRow()
 *
 * @author     David Grudl
 * @package    Nette\Database
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/mysql-nette_test3.sql");



$res = $connection->query("SELECT * FROM types");

Assert::equal( array(
	'int1' => 1,
	'int2' => 1,
	'int3' => 1,
	'int4' => '1', // PHP bug #48724
	'int5' => 1,
	'int6' => 1,
	'int7' => '1', // PHP bug #48724
	'float1' => 1.0,
	'float2' => 1.1,
	'float3' => 1.0,
	'float4' => 1.0,
	'date1' => new Nette\DateTime('2012-10-13'),
	'date2' => new Nette\DateTime('10:10:10'),
	'date3' => new Nette\DateTime('2012-10-13 10:10:10'),
	'date4' => new Nette\DateTime('2012-10-13 10:10:10'),
	'date5' => '2012', // PHP bug #48724
	'str1' => 'a',
	'str2' => 'a',
	'str3' => 'a',
	'str4' => 'a',
	'str5' => NULL,
	'str6' => 'a',
	'str7' => 'a',
	'str8' => 'a',
), (array) $res->fetch() );

Assert::equal( array(
	'int1' => 0,
	'int2' => 0,
	'int3' => 0,
	'int4' => '0', // PHP bug #48724
	'int5' => 0,
	'int6' => 0,
	'int7' => '0', // PHP bug #48724
	'float1' => 0.5,
	'float2' => 0.5,
	'float3' => 0.5,
	'float4' => 0.5,
	'date1' => new Nette\DateTime('0000-00-00 00:00:00'),
	'date2' => new Nette\DateTime('00:00:00'),
	'date3' => new Nette\DateTime('0000-00-00 00:00:00'),
	'date4' => new Nette\DateTime('0000-00-00 00:00:00'),
	'date5' => '2000', // PHP bug #48724
	'str1' => '',
	'str2' => '',
	'str3' => NULL,
	'str4' => '',
	'str5' => NULL,
	'str6' => '',
	'str7' => 'b',
	'str8' => '',
), (array) $res->fetch() );

Assert::equal( array(
	'int1' => NULL,
	'int2' => NULL,
	'int3' => NULL,
	'int4' => NULL,
	'int5' => NULL,
	'int6' => NULL,
	'int7' => NULL,
	'float1' => NULL,
	'float2' => NULL,
	'float3' => NULL,
	'float4' => NULL,
	'date1' => NULL,
	'date2' => NULL,
	'date3' => NULL,
	'date4' => NULL,
	'date5' => NULL,
	'str1' => NULL,
	'str2' => NULL,
	'str3' => NULL,
	'str4' => NULL,
	'str5' => NULL,
	'str6' => NULL,
	'str7' => NULL,
	'str8' => NULL,
), (array) $res->fetch() );
