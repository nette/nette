<?php

/**
 * Test: Nette\Database\Table: Caching.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$connection->setCacheStorage($cacheStorage);
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection($cacheStorage));



// Testing Selection caching
$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT * FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$book = $bookSelection->fetch();
$book->title;
$book->translator;
$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT `id`, `title`, `translator_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$book = $bookSelection->fetch();
$book->author_id;
Assert::same('SELECT * FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());

$bookSelection->__destruct();
$bookSelection = $connection->table('book')->find(2);
Assert::same('SELECT `id`, `title`, `translator_id`, `author_id` FROM `book` WHERE (`id` = ?)', $bookSelection->getSql());



// Testing GroupedSelection reinvalidation caching
$stack = array();
foreach ($connection->table('author') as $author) {
	$stack[] = $selection = $author->related('book');
	$book = $selection->where('translator_id', 12)->fetch();
	if ($book) {
		$book->title; // will affect only the second loop run
	}
}

reset($stack)->__destruct(); // save cache of the first selection, which stores affected columns for all runs

$books = array();
foreach ($connection->table('author') as $author) {
	$selection = $author->related('book');
	$book = $selection->where('translator_id', 12)->fetch();
	if ($book) {
		$books[$book->title] = $book->translator_id; // translator_id is the new used column in the second loop run
	}
}

Assert::same(array(
	'Nette' => 12,
), $books);
