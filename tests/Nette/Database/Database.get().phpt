<?php

/**
 * Test: Nette\Database Single row detail.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$book = $connection->table('book')->get(1);
$data = array(
	'id' => 1,
	'author_id' => 11,
	'translator_id' => 11,
	'title' => '1001 tipu a triku pro PHP',
);

Assert::equal($data, iterator_to_array($book));
