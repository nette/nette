<?php

/**
 * Test: Nette\Database\Table: Related().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$apps1 = $apps2 = array();

foreach ($connection->table('author') as $author) {  // SELECT * FROM `author`
	foreach ($author->related('book', 'translator_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`translator_id` IN (11, 12))
		$apps1[$book->title] = $author->name;
	}

	foreach ($author->related('book.author_id') as $book) {  // SELECT * FROM `book` WHERE (`book`.`author_id` IN (11, 12))
		$apps2[$book->title] = $author->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps1);

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps2);



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



// related() applied twice
$log = array();
foreach ($connection->table('company') as $company) {
	$log[] = "Company $company->id";
	foreach($company->related('author') as $author) {
		$log[] = "Author $author->id";
		foreach($author->related('book') as $book) {
			$log[] = "Book $book->id";
		}
	}
}

Assert::same( array(
	"Company 41",
	"Author 11",
	"Book 1",
	"Book 2",
	"Company 42",
	"Author 12",
	"Book 3",
	"Book 4",
	"Company 43",
	"Author 13",
), $log );



// related() on selection where first row has no related items
$log = array();
foreach ($connection->table('author')->order('id DESC') as $author) {
	foreach($author->related('book') as $book) {
		$log[] = $book->author->id;
	}
}
Assert::same( array(12, 12, 11, 11), $log );

