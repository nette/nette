<?php

/**
 * Test: Nette\Database\ResultSet::normalizeRow()
 *
 * @author     Miloslav Hůla
 * @dataProvider? databases.ini  sqlsrv
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/files/sqlsrv-nette_test3.sql');


$res = $context->query('SELECT * FROM types');

Assert::equal( array(
	'bigint' => 1,
	'binary_3' => '0000FF',
	'bit' => '1',
	'char_5' => 'a    ',
	'date' => new Nette\DateTime('2012-10-13 00:00:00'),
	'datetime' => new Nette\DateTime('2012-10-13 10:10:10'),
	'datetime2' => new Nette\DateTime('2012-10-13 10:10:10'),
	'decimal' => 1.0,
	'float' => '1.1000000000000001',
	'geography' => 'E610000001148716D9CEF7D34740D7A3703D0A975EC08716D9CEF7D34740CBA145B6F3955EC0',
	'geometry' => '0000000001040300000000000000000059400000000000005940000000000000344000000000008066400000000000806640000000000080664001000000010000000001000000FFFFFFFF0000000002',
	'hierarchyid' => '58',
	'int' => 1,
	'money' => 1111.1,
	'nchar' => 'a',
	'ntext' => 'a',
	'numeric_10_0' => 1.0,
	'numeric_10_2' => 1.1,
	'nvarchar' => 'a',
	'real' => 1.1,
	'smalldatetime' => new Nette\DateTime('2012-10-13 10:10:00'),
	'smallint' => 1,
	'smallmoney' => 1.1,
	'text' => 'a',
	'time' => new Nette\DateTime('10:10:10'),
	'tinyint' => 1,
	'uniqueidentifier' => '678E9994-A048-11E2-9030-003048D30C14',
	'varbinary' => '01',
	'varchar' => 'a',
	'xml' => '<doc/>',
), (array) $res->fetch() );

Assert::equal( array(
	'bigint' => 0,
	'binary_3' => '000000',
	'bit' => '0',
	'char_5' => '     ',
	'date' => new Nette\DateTime('0001-01-01 00:00:00'),
	'datetime' => new Nette\DateTime('1753-01-01 00:00:00'),
	'datetime2' => new Nette\DateTime('0001-01-01 00:00:00'),
	'decimal' => 0.0,
	'float' => 0.5,
	'geography' => NULL,
	'geometry' => NULL,
	'hierarchyid' => '',
	'int' => 0,
	'money' => 0.0,
	'nchar' => ' ',
	'ntext' => '',
	'numeric_10_0' => 0.0,
	'numeric_10_2' => 0.5,
	'nvarchar' => '',
	'real' => 0.0,
	'smalldatetime' => new Nette\DateTime('1900-01-01 00:00:00'),
	'smallint' => 0,
	'smallmoney' => 0.5,
	'text' => '',
	'time' => new Nette\DateTime('00:00:00'),
	'tinyint' => 0,
	'uniqueidentifier' => '00000000-0000-0000-0000-000000000000',
	'varbinary' => '00',
	'varchar' => '',
	'xml' => '',
), (array) $res->fetch() );

Assert::same( array(
	'bigint' => NULL,
	'binary_3' => NULL,
	'bit' => NULL,
	'char_5' => NULL,
	'date' => NULL,
	'datetime' => NULL,
	'datetime2' => NULL,
	'decimal' => NULL,
	'float' => NULL,
	'geography' => NULL,
	'geometry' => NULL,
	'hierarchyid' => NULL,
	'int' => NULL,
	'money' => NULL,
	'nchar' => NULL,
	'ntext' => NULL,
	'numeric_10_0' => NULL,
	'numeric_10_2' => NULL,
	'nvarchar' => NULL,
	'real' => NULL,
	'smalldatetime' => NULL,
	'smallint' => NULL,
	'smallmoney' => NULL,
	'text' => NULL,
	'time' => NULL,
	'tinyint' => NULL,
	'uniqueidentifier' => NULL,
	'varbinary' => NULL,
	'varchar' => NULL,
	'xml' => NULL,
), (array) $res->fetch() );


$res = $context->query('SELECT [int] AS a, [text] AS a FROM types');

Assert::same( array(
	'a' => 'a',
), (array) @$res->fetch() );


function isTimestamp($str) {
	return is_string($str) && preg_match('#[0-9A-F]{16}#', $str);
}

$row = (array) $context->query('SELECT [datetimeoffset], CAST([sql_variant] AS int) AS [sql_variant], [timestamp] FROM types2 WHERE id = 1')->fetch();
Assert::type( 'Nette\DateTime', $row['datetimeoffset'] );
Assert::same($row['datetimeoffset']->format('Y-m-d H:i:s P'), '2012-10-13 10:10:10 +02:00');
Assert::same($row['sql_variant'], 123456);
Assert::true(isTimestamp($row['timestamp']));

$row = (array) $context->query('SELECT [datetimeoffset], CAST([sql_variant] AS varchar) AS [sql_variant], [timestamp] FROM types2 WHERE id = 2')->fetch();
Assert::type( 'Nette\DateTime', $row['datetimeoffset'] );
Assert::same($row['datetimeoffset']->format('Y-m-d H:i:s P'), '0001-01-01 00:00:00 +00:00');
Assert::same($row['sql_variant'], 'abcd');
Assert::true(isTimestamp($row['timestamp']));

$row = (array) $context->query('SELECT [datetimeoffset], CAST([sql_variant] AS int) AS [sql_variant], [timestamp] FROM types2 WHERE id = 3')->fetch();
Assert::same($row['datetimeoffset'], NULL);
Assert::same($row['sql_variant'], NULL);
Assert::true(isTimestamp($row['timestamp']));
