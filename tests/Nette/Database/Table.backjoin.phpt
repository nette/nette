<?php

/**
 * Test: Nette\Database\Table: Backward join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");

use Tester\Assert;



$authorTagsCount = array();
$authors = $connection
	->table('author')
	->select('author.name, COUNT(DISTINCT :book:book_tag.tag_id) AS tagsCount')
	->group('author.name')
	->having('COUNT(DISTINCT :book:book_tag.tag_id) < 3')
	->order('tagsCount DESC');

foreach ($authors as $author) {
	$authorTagsCount[$author->name] = $author->tagsCount;
}

Assert::same(array(
	'David Grudl' => 2,
	'Geek' => 0,
), $authorTagsCount);



/*
$count = $connection->table('author')->where(':book.title LIKE ?', '%PHP%')->count('*'); // by translator_id
Assert::same(1, $count);
*/
