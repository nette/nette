<?php

/**
 * Test: Nette\Database\Table: Find one item by URL.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @databases  mysql
 */

require __DIR__ . '/connect.inc.php'; // create $connection, provide $driverName

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/nette_test_{$driverName}1.sql");



$tags = array();
$book = $connection->table('book')->where('title', '1001 tipu a triku pro PHP')->fetch();  // SELECT * FROM `book` WHERE (`title` = ?)
foreach ($book->related('book_tag')->where('tag_id', 21) as $book_tag) {  // SELECT * FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1)) AND (`tag_id` = 21)
	$tags[] = $book_tag->tag->name;  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (21))
}

Assert::same(array('PHP'), $tags);
