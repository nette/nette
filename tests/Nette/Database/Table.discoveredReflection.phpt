<?php

/**
 * Test: Nette\Database\Table: Basic operations with DiscoveredReflection.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



require_once __DIR__ . '/connect.inc.php';
$connection->setDatabaseReflection(new Database\Reflection\DiscoveredReflection);



$appTags = array();
foreach ($connection->table('book') as $book) {
	$appTags[$book->title] = array(
		'author' => $book->author->name,
		'tags' => array(),
	);

	foreach ($book->related('book_tag') as $book_tag) {
		$appTags[$book->title]['tags'][] = $book_tag->tag->name;
	}
}

Assert::equal(array(
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
), $appTags);




$book = $connection->table('book')->get(1);
Assert::equal('Jakub Vrana', $book->translator->name);
