<?php

/**
 * Test: Nette\Database\Table: Backward join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @databases  mysql, pgsql
 */

require __DIR__ . '/connect.inc.php'; // create $connection, provide $driverName

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/nette_test_{$driverName}1.sql");



$authorTagsCount = array();
foreach ($connection->table('author')->select('author.id, author.name, COUNT(DISTINCT book:book_tag:tag_id) AS tagsCount')->group('author.id, author.name')->order('tagsCount DESC') as $author) {
	$authorTagsCount[$author->name] = $author->tagsCount;
}

Assert::same(array(
	'Jakub Vrana' => 3,
	'David Grudl' => 2,
), $authorTagsCount);
