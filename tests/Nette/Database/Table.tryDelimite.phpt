<?php

/**
 * Test: Nette\Database\Table: tryDelimite.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");

use Tester\Assert;
use Nette\Database\Reflection\ConventionalReflection;


$sqlBuilder = new Nette\Database\Table\SqlBuilder('book', $connection, new ConventionalReflection);
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
		Assert::same('HELLO("world", \'column\', \'next-column\')', $tryDelimite->invoke($sqlBuilder, "HELLO(world, 'column', 'next-column')"));
		break;
	case 'mysql':
		Assert::same('`hello`', $tryDelimite->invoke($sqlBuilder, 'hello'));
		Assert::same(' `hello` ', $tryDelimite->invoke($sqlBuilder, ' hello '));
		Assert::same('HELLO', $tryDelimite->invoke($sqlBuilder, 'HELLO'));
		Assert::same('`HellO`', $tryDelimite->invoke($sqlBuilder, 'HellO'));
		Assert::same('`hello`.`world`', $tryDelimite->invoke($sqlBuilder, 'hello.world'));
		Assert::same('`hello` `world`', $tryDelimite->invoke($sqlBuilder, 'hello world'));
		Assert::same('HELLO(`world`)', $tryDelimite->invoke($sqlBuilder, 'HELLO(world)'));
		Assert::same('hello(`world`)', $tryDelimite->invoke($sqlBuilder, 'hello(world)'));
		Assert::same('`hello`', $tryDelimite->invoke($sqlBuilder, '`hello`'));
		Assert::same("HELLO(`world`, 'column', 'next-column')", $tryDelimite->invoke($sqlBuilder, "HELLO(world, 'column', 'next-column')"));
		break;
}
