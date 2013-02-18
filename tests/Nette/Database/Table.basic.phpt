<?php

/**
 * Test: Nette\Database\Table: Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$book = $connection->table('book')->where('id = ?', 1)->select('id, title')->fetch()->toArray();  // SELECT `id`, `title` FROM `book` WHERE (`id` = ?)
Assert::same(array(
	'id' => 1,
	'title' => '1001 tipu a triku pro PHP',
), $book);

$book = $connection->table('book')->select('id, title')->where('id = ?', 1)->fetch()->toArray();  // SELECT `id`, `title` FROM `book` WHERE (`id` = ?)
Assert::same(array(
	'id' => 1,
	'title' => '1001 tipu a triku pro PHP',
), $book);

$book = $connection->table('book')->get(1);
Assert::exception(function() use ($book) {
	$book->unknown_column;
}, 'Nette\MemberAccessException', 'Cannot read an undeclared column "unknown_column".');



$bookTags = array();
foreach ($connection->table('book') as $book) {  // SELECT * FROM `book`
	$bookTags[$book->title] = array(
		'author' => $book->author->name,  // SELECT * FROM `author` WHERE (`author`.`id` IN (11, 12))
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {  // SELECT * FROM `book_tag` WHERE (`book_tag`.`book_id` IN (1, 2, 3, 4))
		$bookTags[$book->title]['tags'][] = $book_tag->tag->name;  // SELECT * FROM `tag` WHERE (`tag`.`id` IN (21, 22, 23))
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('PHP', 'MySQL'),
	),
	'JUSH' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('JavaScript'),
	),
	'Nette' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP'),
	),
	'Dibi' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP', 'MySQL'),
	),
), $bookTags);



$sql = $connection->table('book')
	->where(new Nette\Database\SqlLiteral('id = 1'))
	->where(new Nette\Database\SqlLiteral('id = 1'))
	->getSql();


switch ($driverName) {
	case 'mysql':
		Assert::same('SELECT * FROM `book` WHERE (`id` = 1)', $sql);
		break;

	case 'pgsql':
		Assert::same('SELECT * FROM "book" WHERE ("id" = 1)', $sql);
		break;
}



$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection);

$book = $connection->table('book')->get(1);
Assert::throws(function() use ($book) {
	$book->test;
}, 'Nette\MemberAccessException', 'Cannot read an undeclared column "test".');

Assert::throws(function() use ($book) {
	$book->ref('test');
}, 'Nette\Database\Reflection\MissingReferenceException', 'No reference found for $book->test.');

Assert::throws(function() use ($book) {
	$book->related('test');
}, 'Nette\Database\Reflection\MissingReferenceException', 'No reference found for $book->related(test).');
