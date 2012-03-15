<?php

/**
 * Test: Nette\Database\Table: tryDelimite.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$table = $connection->table('book');
$tryDelimite = $table->reflection->getMethod('tryDelimite');
$tryDelimite->setAccessible(TRUE);

Assert::same('`hello`', $tryDelimite->invoke($table, 'hello'));
Assert::same(' `hello` ', $tryDelimite->invoke($table, ' hello '));
Assert::same('HELLO', $tryDelimite->invoke($table, 'HELLO'));
Assert::same('`HellO`', $tryDelimite->invoke($table, 'HellO'));
Assert::same('`hello`.`world`', $tryDelimite->invoke($table, 'hello.world'));
Assert::same('`hello` `world`', $tryDelimite->invoke($table, 'hello world'));
Assert::same('HELLO(`world`)', $tryDelimite->invoke($table, 'HELLO(world)'));
Assert::same('hello(`world`)', $tryDelimite->invoke($table, 'hello(world)'));
Assert::same('`hello`', $tryDelimite->invoke($table, '`hello`'));
