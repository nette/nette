<?php

/**
 * Test: Nette\Database Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$bookExpected = array(
	'id' => 1,
	'title' => '1001 tipu a triku pro PHP',
);

$book = iterator_to_array($connection->table('book')->where('id = ?', 1)->select('id, title')->fetch());
Assert::equal($bookExpected, $book);

$book = iterator_to_array($connection->table('book')->select('id, title')->where('id = ?', 1)->fetch());
Assert::equal($bookExpected, $book);



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
