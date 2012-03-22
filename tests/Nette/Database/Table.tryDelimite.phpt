<?php

/**
 * Test: Nette\Database\Table: tryDelimite.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @databases  mysql, pgsql
 */

require __DIR__ . '/connect.inc.php'; // create $connection, provide $driverName

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/nette_test_{$driverName}1.sql");



$table = $connection->table('book');
$tryDelimite = $table->reflection->getMethod('tryDelimite');
$tryDelimite->setAccessible(TRUE);

Assert::same('HELLO', $tryDelimite->invoke($table, 'HELLO'));

switch ($driverName) {
	case 'pgsql':
		Assert::same('"hello"', $tryDelimite->invoke($table, 'hello'));
		Assert::same(' "hello" ', $tryDelimite->invoke($table, ' hello '));
		Assert::same('"HellO"', $tryDelimite->invoke($table, 'HellO'));
		Assert::same('"hello"."world"', $tryDelimite->invoke($table, 'hello.world'));
		Assert::same('"hello" "world"', $tryDelimite->invoke($table, 'hello world'));
		Assert::same('HELLO("world")', $tryDelimite->invoke($table, 'HELLO(world)'));
		Assert::same('hello("world")', $tryDelimite->invoke($table, 'hello(world)'));
		Assert::same('"hello"', $tryDelimite->invoke($table, '"hello"'));
		break;

	default:
		Assert::same('`hello`', $tryDelimite->invoke($table, 'hello'));
		Assert::same(' `hello` ', $tryDelimite->invoke($table, ' hello '));
		Assert::same('`HellO`', $tryDelimite->invoke($table, 'HellO'));
		Assert::same('`hello`.`world`', $tryDelimite->invoke($table, 'hello.world'));
		Assert::same('`hello` `world`', $tryDelimite->invoke($table, 'hello world'));
		Assert::same('HELLO(`world`)', $tryDelimite->invoke($table, 'HELLO(world)'));
		Assert::same('hello(`world`)', $tryDelimite->invoke($table, 'hello(world)'));
		Assert::same('`hello`', $tryDelimite->invoke($table, '`hello`'));
		break;
}
