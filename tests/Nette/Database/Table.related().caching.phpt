<?php

/**
 * Test: Nette\Database\Table: Shared related data caching.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($dao) {
	$books = $dao->table('book');
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
});


test(function() use ($connection, $dao) {
	$connection->query('UPDATE book SET translator_id = 12 WHERE id = 2');
	$author = $dao->table('author')->get(11);

	foreach ($author->related('book')->limit(1) as $book) {
		$book->ref('author', 'translator_id')->name;
	}

	$translators = array();
	foreach ($author->related('book')->limit(2) as $book) {
		$translators[] = $book->ref('author', 'translator_id')->name;
	}
	sort($translators);

	Assert::same(array(
		'David Grudl',
		'Jakub Vrana',
	), $translators);
});
