<?php

/**
 * Test: bug 1356
 * @dataProvider? ../../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../../connect.inc.php';

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../../files/{$driverName}-nette_test1.sql");


$books = $context->table('book')->limit(1);
foreach ($books as $book) $book->id;
$books->__destruct();


$books = $context->table('book')->limit(1);
foreach ($books as $book) {
	$book->title;
}

Assert::same(reformat('SELECT * FROM [book] LIMIT 1'), $books->getSql());
