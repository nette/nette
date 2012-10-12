<?php

/**
 * Test: Nette\Database\Table: tryDelimite.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

use Nette\Database\SqlLiteral;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$sqlBuilder = new Nette\Database\Table\SqlBuilder($connection->table('book'));
$tryDelimite = $sqlBuilder->reflection->getMethod('tryDelimite');
$tryDelimite->setAccessible(TRUE);

switch ($driverName) {
	case 'pgsql':
		Assert::same('"hello"', $tryDelimite->invoke($sqlBuilder, 'hello'));
		Assert::same(' "hello" ', $tryDelimite->invoke($sqlBuilder, ' hello '));
		Assert::same('HELLO', $tryDelimite->invoke($sqlBuilder, 'HELLO'));
		Assert::same('"HellO"', $tryDelimite->invoke($sqlBuilder, 'HellO'));
		Assert::same('"hello"."world"', $tryDelimite->invoke($sqlBuilder, 'hello.world'));
		Assert::same('"hello" "world"', $tryDelimite->invoke($sqlBuilder, 'hello world'));
		Assert::same('HELLO("world")', $tryDelimite->invoke($sqlBuilder, 'HELLO(world)'));
		Assert::same('hello("world")', $tryDelimite->invoke($sqlBuilder, 'hello(world)'));
		Assert::same('"hello"', $tryDelimite->invoke($sqlBuilder, '"hello"'));
		break;
	case 'mysql':
	default:
		Assert::same('`hello`', $tryDelimite->invoke($sqlBuilder, 'hello'));
		Assert::same(' `hello` ', $tryDelimite->invoke($sqlBuilder, ' hello '));
		Assert::same('HELLO', $tryDelimite->invoke($sqlBuilder, 'HELLO'));
		Assert::same('`HellO`', $tryDelimite->invoke($sqlBuilder, 'HellO'));
		Assert::same('`hello`.`world`', $tryDelimite->invoke($sqlBuilder, 'hello.world'));
		Assert::same('`hello` `world`', $tryDelimite->invoke($sqlBuilder, 'hello world'));
		Assert::same('HELLO(`world`)', $tryDelimite->invoke($sqlBuilder, 'HELLO(world)'));
		Assert::same('hello(`world`)', $tryDelimite->invoke($sqlBuilder, 'hello(world)'));
		Assert::same('`hello`', $tryDelimite->invoke($sqlBuilder, '`hello`'));
		break;
}

//sqlLiterals
Assert::same('hello', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('hello')));
Assert::same(' hello ', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral(' hello ')));
Assert::same('HELLO', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('HELLO')));
Assert::same('HellO', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('HellO')));
Assert::same('hello.world', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('hello.world')));
Assert::same('hello world', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('hello world')));
Assert::same('HELLO(world)', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('HELLO(world)')));
Assert::same('hello(world)', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('hello(world)')));
Assert::same('hello', (string) $tryDelimite->invoke($sqlBuilder, new SqlLiteral('hello')));