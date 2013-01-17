<?php

/**
 * Test: Nette\Database\Table: Related().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$books1 = $books2 = $books3 = array();

foreach ($connection->table('author') as $author) {  // SELECT * FROM `author`
	foreach ($author->related('book', 'translator_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`translator_id` IN (11, 12, 13))
		$books1[$book->title] = $author->name;
	}

	foreach ($author->related('book.author_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`author_id` IN (11, 12, 13))
		$books2[$book->title] = $author->name;
	}

	foreach ($author->related('book') as $book) {  // SELECT * FROM `book` WHERE (`book`.`author_id` IN (11, 12, 13))
		$books3[$book->title] = $author->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $books1);

$expectBooks = array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
);

Assert::same($expectBooks, $books2);
Assert::same($expectBooks, $books3);



$tagsAuthors = array();
foreach ($connection->table('tag') as $tag) {

	$book_tags = $tag->related('book_tag')->group('book_tag.tag_id, book.author_id, book.author.name')->select('book.author_id')->order('book.author.name');
	foreach ($book_tags as $book_tag) {
		$tagsAuthors[$tag->name][] = $book_tag->ref('author', 'author_id')->name;
	}

}

Assert::same(array(
	'PHP' => array(
		'David Grudl',
		'Jakub Vrana',
	),
	'MySQL' => array(
		'David Grudl',
		'Jakub Vrana',
	),
	'JavaScript' => array(
		'Jakub Vrana',
	),
), $tagsAuthors);



$counts1 = $counts2 = array();
foreach($connection->table('author')->order('id') as $author) {
	$counts1[] = $author->related('book.author_id')->count('id');
	$counts2[] = $author->related('book.author_id')->where('translator_id', NULL)->count('id');
}

Assert::same(array(2, 2, 0), $counts1);
Assert::same(array(1, 0, 0), $counts2);



$author = $connection->table('author')->get(11);
$books  = $author->related('book')->where('translator_id', 11);
Assert::same('1001 tipu a triku pro PHP', $books->fetch()->title);
Assert::false($books->fetch());

Assert::same('1001 tipu a triku pro PHP', $author->related('book')->fetch()->title);

Assert::same('JUSH', $author->related('book', NULL, TRUE)->where('translator_id', NULL)->fetch()->title);
