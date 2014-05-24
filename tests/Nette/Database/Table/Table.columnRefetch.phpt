<?php

/**
 * Test: Nette\Database\Table: Refetching rows with all columns
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


$books = $context->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$book->title;
}
$books->__destruct();

$res = array();
$books = $context->table('book')->order('id DESC')->limit(2);
foreach ($books as $book) {
	$res[] = (string) $book->title;
}
Assert::same(array('Dibi', 'Nette'), $res);

$context->table('book')->insert(array(
	'title' => 'New book #1',
	'author_id' => 11,
));
$context->table('book')->insert(array(
	'title' => 'New book #2',
	'author_id' => 11,
));

$res = array();
foreach ($books as $book) {
	$res[] = (string) $book->title;
	$res[] = (string) $book->author->name;
}
Assert::same(array('Dibi', 'David Grudl', 'Nette', 'David Grudl'), $res);
