<?php

/**
 * Test: Nette\Database\Table: Subqueries.
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
	$unknownBorn = $dao->table('author')->where('born', null); // authors with unknown date of born
	foreach ($dao->table('book')->where('author_id', $unknownBorn) as $book) { // their books: SELECT `id` FROM `author` WHERE (`born` IS NULL), SELECT * FROM `book` WHERE (`author_id` IN (11, 12))
		$apps[] = $book->title;
	}

	Assert::same(array(
		'1001 tipu a triku pro PHP',
		'JUSH',
		'Nette',
		'Dibi',
	), $apps);
});
