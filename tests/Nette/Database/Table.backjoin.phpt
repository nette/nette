<?php

/**
 * Test: Nette\Database\Table: Backward join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($dao) {
	$authorTagsCount = array();
	$authors = $dao
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
});


test(function() use ($dao) {
	$authorsSelection = $dao->table('author')->where(':book.translator_id IS NOT NULL')->wherePrimary(12);
	Assert::same(reformat('SELECT [author].* FROM [author] LEFT JOIN [book] ON [author].[id] = [book].[author_id] WHERE ([book].[translator_id] IS NOT NULL) AND ([author].[id] = ?)'), $authorsSelection->getSql());

	$authors = array();
	foreach ($authorsSelection as $author) {
		$authors[$author->id] = $author->name;
	}

	Assert::same(array(12 => 'David Grudl'), $authors);
});


test(function() use ($dao) {
	/*
	$count = $dao->table('author')->where(':book.title LIKE ?', '%PHP%')->count('*'); // by translator_id
	Assert::same(1, $count);
	*/
});
