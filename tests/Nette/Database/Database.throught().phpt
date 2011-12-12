<?php

/**
 * Test: Nette\Database Through().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$apps = array();
foreach ($connection->table('author') as $author) {
	foreach ($author->related('book')->through('translator_id') as $book) {
		$apps[$book->title] = $author->name;
	}
}

Assert::equal(array(
	'1001 tipu a triku pro PHP' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps);
