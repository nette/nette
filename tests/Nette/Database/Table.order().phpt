<?php

/**
 * Test: Nette\Database\Table: Search and order items.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



test(function() use ($dao) {
	$apps = array();
	foreach ($dao->table('book')->where('title LIKE ?', '%t%')->order('title')->limit(3) as $book) {  // SELECT * FROM `book` WHERE (`title` LIKE ?) ORDER BY `title` LIMIT 3
		$apps[] = $book->title;
	}

	Assert::same(array(
		'1001 tipu a triku pro PHP',
		'Nette',
	), $apps);
});
