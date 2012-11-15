<?php

/**
 * Test: Nette\Database\Table: Basic operations with DiscoveredReflection.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");
$connection->setDatabaseReflection(new Nette\Database\Reflection\DiscoveredReflection($connection));



$appTags = array();
foreach ($connection->table('book') as $book) {
	$appTags[$book->title] = array(
		'author' => $book->author->name,
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {
		$appTags[$book->title]['tags'][] = $book_tag->tag->name;
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
), $appTags);



$books = array();
foreach ($connection->table('author') as $author) {
	foreach ($author->related('book') as $book) {
		$books[$book->title] = $author->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $books);



$book = $connection->table('book')->get(1);
Assert::same('Jakub Vrana', $book->translator->name);



$book = $connection->table('book')->get(2);
Assert::true(isset($book->author_id));
Assert::false(empty($book->author_id));

Assert::false(isset($book->translator_id));
Assert::true(empty($book->translator_id));
Assert::false(isset($book->test));

Assert::false(isset($book->author));
Assert::false(isset($book->translator));
Assert::true(empty($book->author));
Assert::true(empty($book->translator));



if (
	$connection->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' &&
	($lowerCase = $connection->query('SHOW VARIABLES LIKE "lower_case_table_names"')->fetch()) &&
	$lowerCase->Value != 0
) {
	// tests case-insensitive reflection
	$books = array();
	foreach ($connection->table('Author') as $author) {
		foreach ($author->related('book') as $book) {
			$books[$book->title] = $author->name;
		}
	}

	Assert::same(array(
		'1001 tipu a triku pro PHP' => 'Jakub Vrana',
		'JUSH' => 'Jakub Vrana',
		'Nette' => 'David Grudl',
		'Dibi' => 'David Grudl',
	), $books);
}
