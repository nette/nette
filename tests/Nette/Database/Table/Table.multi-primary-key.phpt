<?php

/**
 * Test: Nette\Database\Table: Multi primary key support.
 *
 * @author     Jan Skrasek
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


$cacheStorage = new Nette\Caching\Storages\MemoryStorage;
$context = new Nette\Database\Context(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage),
	$cacheStorage
);


test(function() use ($context) {
	$book = $context->table('book')->get(1);
	foreach ($book->related('book_tag') as $bookTag) {
		if ($bookTag->tag->name === 'PHP') {
			$bookTag->delete();
		}
	}

	$count = $book->related('book_tag')->count();
	Assert::same(1, $count);

	$count = $book->related('book_tag')->count('*');
	Assert::same(1, $count);

	$count = $context->table('book_tag')->count('*');
	Assert::same(5, $count);
});


test(function() use ($context) {
	$book = $context->table('book')->get(3);
	foreach ($related = $book->related('book_tag_alt') as $bookTag) {
	}
	$related->__destruct();

	$states = array();
	$book = $context->table('book')->get(3);
	foreach ($book->related('book_tag_alt') as $bookTag) {
		$states[] = $bookTag->state;
	}

	Assert::same(array(
		'public',
		'private',
		'private',
		'public',
	), $states);
});


test(function() use ($context) {
	$context->table('book_tag')->insert(array(
		'book_id' => 1,
		'tag_id' => 21, // PHP tag
	));
	$count = $context->table('book_tag')->where('book_id', 1)->count('*');
	Assert::same(2, $count);
});
