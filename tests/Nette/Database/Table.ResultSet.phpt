<?php

/**
 * Test: Nette\Database\Table\NativeResultSet.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database\Table
 * @dataProvider? databases.ini
 */

use Tester\Assert;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Reflection\DiscoveredReflection;
use Nette\Database\Table\ResultSet;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");

header('Content-type: text/html');
Nette\Diagnostics\Debugger::enable(FALSE);
Nette\Diagnostics\Debugger::$strictMode = TRUE;
Nette\Database\Helpers::createDebugPanel($connection);
Nette\Diagnostics\Debugger::$blueScreen->addPanel('Nette\Database\Diagnostics\ConnectionPanel::renderException');

$cacheStorage = new MemoryStorage;



$res = $connection->query('SELECT * FROM book ORDER BY id');
$resultSet = new ResultSet('book', $res, $connection, new DiscoveredReflection($connection, $cacheStorage), $cacheStorage);

$bookTags = array();
foreach ($resultSet as $book) {
	$bookTags[$book->title] = array(
		'author' => $book->author->name,
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {
		$bookTags[$book->title]['tags'][] = $book_tag->tag->name;
	}
}

Assert::same(array(
	'1001 tipu a triku pro PHP' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('PHP', 'MySQL'),
	),
	'JUSH' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('JavaScript'),
	),
	'Nette' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP'),
	),
	'Dibi' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP', 'MySQL'),
	),
), $bookTags);
