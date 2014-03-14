<?php

/**
 * Test: Nette\Database\Table: DiscoveredReflection with self-reference.
 *
 * @author     Jan Skrasek
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;
use Nette\Database;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


$context->query('UPDATE book SET next_volume = 3 WHERE id IN (2,4)');


test(function() use ($connection, $context) {
	$book = $context->table('book')->get(4);
	Assert::same('Nette', $book->volume->title);
	Assert::same('Nette', $book->ref('book', 'next_volume')->title);
});


test(function() use ($context) {
	$book = $context->table('book')->get(3);
	Assert::same(2, $book->related('book.next_volume')->count('*'));
	Assert::same(2, $book->related('book', 'next_volume')->count('*'));
});
