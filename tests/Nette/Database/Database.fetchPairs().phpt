<?php

/**
 * Test: Nette\Database Fetch pairs.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$apps = $connection->table('book')->order('title')->fetchPairs('id', 'title');
Assert::equal(array(
	1 => '1001 tipu a triku pro PHP',
	4 => 'Dibi',
	2 => 'JUSH',
	3 => 'Nette',
), $apps);




$ids = $connection->table('book')->order('id')->fetchPairs('id', 'id');
Assert::equal(array(
	1 => '1',
	2 => '2',
	3 => '3',
	4 => '4',
), $ids);
