<?php

/**
 * Test: Nette\Database\Table: Caching.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$dao = new Nette\Database\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
);



// Testing Selection caching
$bookSelection = $dao->table('book')->wherePrimary(2);
Assert::same(reformat('SELECT * FROM [book] WHERE ([book].[id] = ?)'), $bookSelection->getSql());


$book = $bookSelection->fetch();
$book->title;
$book->translator;
$bookSelection->__destruct();
$bookSelection = $dao->table('book')->wherePrimary(2);
Assert::same(reformat('SELECT [id], [title], [translator_id] FROM [book] WHERE ([book].[id] = ?)'), $bookSelection->getSql());


$book = $bookSelection->fetch();
$book->author_id;
Assert::same(reformat('SELECT * FROM [book] WHERE ([book].[id] = ?)'), $bookSelection->getSql());

$bookSelection->__destruct();
$bookSelection = $dao->table('book')->wherePrimary(2);
Assert::same(reformat('SELECT [id], [title], [translator_id], [author_id] FROM [book] WHERE ([book].[id] = ?)'), $bookSelection->getSql());



// Testing GroupedSelection reinvalidation caching
foreach ($dao->table('author') as $author) {
	$stack[] = $selection = $author->related('book.author_id')->order('title');
	foreach ($selection as $book) {
		$book->title;
	}
}

reset($stack)->__destruct();


$books = array();
foreach ($dao->table('author') as $author) {
	foreach ($author->related('book.author_id')->order('title') as $book) {
		if ($book->author_id == 12) {
			$books[$book->title] = $book->translator_id; // translator_id is new used column in the second loop
		}
	}
}

Assert::same(array(
	'Dibi' => 12,
	'Nette' => 12,
), $books);




$cacheStorage->clean(array(Nette\Caching\Cache::ALL => TRUE));



$selection = $dao->table('book');
foreach ($selection as $book) {
	$book->id;
}
$selection->__destruct();

$authors = array();
foreach ($dao->table('book') as $book) {
	$authors[$book->author->name] = 1;
}

$authors = array_keys($authors);
sort($authors);

Assert::same(array(
	'David Grudl',
	'Jakub Vrana',
), $authors);



$cacheStorage->clean(array(Nette\Caching\Cache::ALL => TRUE));



$relatedStack = array();
foreach ($dao->table('author') as $author) {
	$relatedStack[] = $related = $author->related('book.author_id');
	foreach ($related as $book) {
		$book->id;
	}
}

foreach ($relatedStack as $related) {
	$property = $related->reflection->getProperty('accessedColumns');
	$property->setAccessible(TRUE);
	// checks if instances have shared data of accessed columns
	Assert::same(array('id', 'author_id'), array_keys((array) $property->getValue($related)));
}



$cacheStorage->clean(array(Nette\Caching\Cache::ALL => TRUE));



$author = $dao->table('author')->get(11);
$books = $author->related('book')->where('translator_id', 99); // 0 rows
foreach ($books as $book) {}
$books->__destruct();
unset($author);

$author = $dao->table('author')->get(11);
$books = $author->related('book')->where('translator_id', 11);
Assert::same(array('id', 'author_id'), $books->getPreviousAccessedColumns());
