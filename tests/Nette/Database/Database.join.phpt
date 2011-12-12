<?php

/**
 * Test: Nette\Database Join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$apps = array();
foreach ($connection->table('book')->order('author.name, title') as $book) {
	$apps[$book->title] = $book->author->name;
}

Assert::equal(array(
	'Dibi' => 'David Grudl',
	'Nette' => 'David Grudl',
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
), $apps);



$tags = array();
foreach ($connection->table('book_tag')->where('book.author.name', 'Jakub Vrana')->group('book_tag.tag_id') as $book_tag) {
	$tags[] = $book_tag->tag->name;
}

Assert::equal(array(
	'PHP',
	'MySQL',
	'JavaScript',
), $tags);
