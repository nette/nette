<?php

/**
 * Test: Nette\Database\Table: Aggregation functions.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	$count = $context->table('book')->count('*');  // SELECT COUNT(*) FROM `book`
	Assert::same(4, $count);
});


test(function() use ($context) {
	$tags = array();
	foreach ($context->table('book') as $book) {  // SELECT * FROM `book`
		$count = $book->related('book_tag')->count('*');  // SELECT COUNT(*), `book_id` FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1, 2, 3, 4)) GROUP BY `book_id`
		$tags[$book->title] = $count;
	}

	Assert::same(array(
		'1001 tipu a triku pro PHP' => 2,
		'JUSH' => 1,
		'Nette' => 1,
		'Dibi' => 2,
	), $tags);
});


test(function() use ($context) {
	$authors = $context->table('author')->where(':book.translator_id IS NOT NULL')->group('author.id');  // SELECT `author`.* FROM `author` INNER JOIN `book` ON `author`.`id` = `book`.`author_id` WHERE (`book`.`translator_id` IS NOT NULL) GROUP BY `author`.`id`
	Assert::same(2, count($authors));
	Assert::same(2, $authors->count('DISTINCT author.id'));  // SELECT COUNT(DISTINCT author.id) FROM `author` INNER JOIN `book` ON `author`.`id` = `book`.`author_id` WHERE (`book`.`translator_id` IS NOT NULL)
});
