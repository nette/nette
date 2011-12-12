<?php

/**
 * Test: Nette\Database\Table: Insert operations
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once __DIR__ . '/connect.inc.php';



$connection->table('author')->insert(array(
	'id' => 13,
	'name' => 'Edard Stark',
	'web' => 'http://example.com',
));



$insert = array(
	'name' => 'Catelyn Stark',
	'web' => 'http://example.com',
	'born' => new DateTime('2011-11-11'),
);
$connection->table('author')->insert($insert);



$catelynStarkExpected = array(
	'id' => 14,
	'name' => 'Catelyn Stark',
	'web' => 'http://example.com',
	'born' => new DateTime('2011-11-11'),
);

$catelynStark = $connection->table('author')->get(14);
Assert::equal($catelynStarkExpected, iterator_to_array($catelynStark));



$book = $connection->table('book');

$book1 = $book->get(1);
Assert::equal('Jakub Vrana', $book1->author->name);

$book2 = $book->insert(array(
	'title' => 'Winterfell',
	'author_id' => 11,
));

$book3 = $book->insert(array(
	'title' => 'Dragonstone',
	'author_id' => $connection->table('author')->get(13),
));

Assert::equal('Jakub Vrana', $book2->author->name);
Assert::equal('Edard Stark', $book3->author->name);



Assert::throws(function() use ($connection) {
	$connection->table('author')->insert(array(
		'id' => 14,
		'name' => 'John Snow',
		'web' => 'http://example.com',
	));
}, '\PDOException');
