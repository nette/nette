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



$authorTagsCount = array();
foreach ($connection->table('author')->select('author.name, COUNT(DISTINCT book:book_tag:tag_id) AS tagsCount')->group('author.name')->order('tagsCount DESC') as $author) {
	$authorTagsCount[$author->name] = $author->tagsCount;
}

Assert::same(array(
	'Jakub Vrana' => 3,
	'David Grudl' => 2,
	'Geek' => 0,
), $authorTagsCount);
