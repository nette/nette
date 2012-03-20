<?php

/**
 * Test: Nette\Database\Table: Delete operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$connection->table('book_tag')->where('book_id', 4)->delete();  // DELETE FROM `book_tag` WHERE (`book_id` = ?)

$count = $connection->table('book_tag')->where('book_id', 4)->count();  // SELECT * FROM `book_tag` WHERE (`book_id` = ?)
Assert::same(0, $count);



$book = $connection->table('book')->get(3);  // SELECT * FROM `book` WHERE (`id` = ?)
$book->related('book_tag')->delete();  // DELETE FROM `book_tag` WHERE (`book_id` = ?)

$count = $connection->table('book_tag')->where('book_id', 3)->count();  // SELECT * FROM `book_tag` WHERE (`book_id` = ?)
Assert::same(0, $count);



$book->delete();  // DELETE FROM `book` WHERE (`id` = ?)
Assert::same(0, count($connection->table('book')->find(3)));  // SELECT * FROM `book` WHERE (`id` = ?)
