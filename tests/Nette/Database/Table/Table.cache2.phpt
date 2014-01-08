<?php

/**
 * Test: Nette\Database\Table: Special case of caching
 *
 * @author     Jachym Tousek
 * @dataProvider? ../databases.ini
 */

use Tester\Assert;

require __DIR__ . '/../connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../files/{$driverName}-nette_test1.sql");


for ($i = 1; $i <= 2; ++$i) {

	foreach ($context->table('author') as $author) {
		$author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$book->title;
		}
	}

	foreach ($context->table('author')->where('id', 13) as $author) {
		$author->name;
		foreach ($author->related('book', 'author_id') as $book) {
			$book->title;
		}
	}

}
