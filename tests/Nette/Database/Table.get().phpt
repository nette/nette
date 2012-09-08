<?php

/**
 * Test: Nette\Database\Table: Single row detail.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$book = $connection->table('book')->get(1);  // SELECT * FROM `book` WHERE (`id` = ?)

Assert::same(array(
	'id' => 1,
	'author_id' => 11,
	'translator_id' => 11,
	'title' => '1001 tipu a triku pro PHP',
), $book->toArray());
