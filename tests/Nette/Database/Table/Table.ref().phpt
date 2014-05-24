<?php

/**
 * Test: Nette\Database\Table: Reference ref().
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


Assert::same('Jakub Vrana', $context->table('book')->get(1)->ref('author')->name);


test(function() use ($context) {
	$book = $context->table('book')->get(1);
	$book->update(array(
		'translator_id' => 12,
	));


	$book = $context->table('book')->get(1);
	Assert::same('David Grudl', $book->ref('author', 'translator_id')->name);
	Assert::same('Jakub Vrana', $book->ref('author', 'author_id')->name);
});


test(function() use ($context) {
	Assert::null($context->table('book')->get(2)->ref('author', 'translator_id'));
});
