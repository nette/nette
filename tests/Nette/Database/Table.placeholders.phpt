<?php

/**
 * Test: Nette\Database\Table\SqlBuilder: Escaping with SqlLiteral.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;
use Nette\Database\SqlLiteral;
use Nette\Database\Reflection\DiscoveredReflection;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


$reflection = new DiscoveredReflection($connection);
$context = new Nette\Database\Context($connection, $reflection);


test(function() use ($context, $driverName) {
	// Leave literals lower-cased, also not-delimiting them is tested.
	switch ($driverName) {
		case 'mysql':
			$literal = new SqlLiteral('year(now())');
			break;
		case 'pgsql':
			$literal = new SqlLiteral('extract(year from now())::int');
			break;
		case 'sqlite':
			$literal = new SqlLiteral("cast(strftime('%Y', date('now')) as integer)");
			break;
		case 'sqlsrv':
			$literal = new SqlLiteral('year(cast(current_timestamp as datetime))');
			break;
		default:
			Assert::fail("Unsupported driver $driverName");
	}

	$selection = $context
		->table('book')
		->select('? AS col1', 'hi there!')
		->select('? AS col2', $literal);

	$row = $selection->fetch();
	Assert::same('hi there!', $row['col1']);
	Assert::same((int) date('Y'), $row['col2']);
});


test(function() use ($context) {
	$bookTagsCount = array();
	$books = $context
		->table('book')
		->select('book.title, COUNT(DISTINCT :book_tag.tag_id) AS tagsCount')
		->group('book.title')
		->having('COUNT(DISTINCT :book_tag.tag_id) < ?', 2)
		->order('book.title');

	foreach ($books as $book) {
		$bookTagsCount[$book->title] = $book->tagsCount;
	}

	Assert::same(array(
		'JUSH' => 1,
		'Nette' => 1,
	), $bookTagsCount);
});


test(function() use ($context, $driverName) {
	if ($driverName === 'mysql') {
		$authors = array();
		$selection = $context->table('author')->order('FIELD(name, ?)', array('Jakub Vrana', 'David Grudl', 'Geek'));
		foreach ($selection as $author) {
			$authors[] = $author->name;
		}

		Assert::same(array('Jakub Vrana', 'David Grudl', 'Geek'), $authors);
	}
});


test(function() use ($context) { // Test placeholder for GroupedSelection
	$books = $context->table('author')->get(11)->related('book')->order('title = ? DESC', 'Test');
	foreach ($books as $book) {}

	$books = $context->table('author')->get(11)->related('book')->select('SUBSTR(title, ?)', 3);
	foreach ($books as $book) {}
});
