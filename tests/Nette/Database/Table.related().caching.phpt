<?php

/**
 * Test: Nette\Database\Table: Shared related data caching.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$books = $connection->table('book');
foreach ($books as $book) {
	foreach ($book->related('book_tag') as $bookTag) {
		$bookTag->tag;
	}
}

$tags = array();
foreach ($books as $book) {
	foreach ($book->related('book_tag_alt') as $bookTag) {
		$tags[] = $bookTag->tag->name;
	}
}

Assert::same(array(
	'PHP',
	'MySQL',
	'JavaScript',
	'Neon',
), $tags);
