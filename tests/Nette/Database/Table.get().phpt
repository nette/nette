<?php

/**
 * Test: Nette\Database\Table: Single row detail.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$book = $connection->table('book')->get(1)->toArray();  // SELECT * FROM `book` WHERE (`id` = ?)
unset($book['next_volume']); // exists only in Sqlite

Assert::same(array(
	'id' => 1,
	'author_id' => 11,
	'translator_id' => 11,
	'title' => '1001 tipu a triku pro PHP',
), $book);
