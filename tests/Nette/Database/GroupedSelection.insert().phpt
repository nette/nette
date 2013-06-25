<?php

/**
 * Test: Nette\Database\Table\GroupedSelection: Insert operations
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");

$dao = new Nette\Database\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection)
);


test(function() use ($dao) {
	$book = $dao->table('book')->get(1);
	$book->related('book_tag')->insert(array('tag_id' => 23));

	Assert::equal(3, $book->related('book_tag')->count());
	Assert::equal(3, $book->related('book_tag')->count('*'));

	$book->related('book_tag')->where('tag_id', 23)->delete();
});



test(function() use ($dao) { // test counting already fetched rows
	$book = $dao->table('book')->get(1);
	iterator_to_array($book->related('book_tag'));
	$book->related('book_tag')->insert(array('tag_id' => 23));
	Assert::equal(3, $book->related('book_tag')->count());
});
