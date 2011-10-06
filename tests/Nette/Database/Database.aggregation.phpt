<?php

/**
 * Test: Nette\Database Aggregation functions.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$count = $connection->table('application')->count('*');
Assert::equal(4, $count);

$tags = array();
foreach ($connection->table('application') as $application) {
	$count = $application->related('application_tag')->count('*');
	$tags[$application->title] = $count;
}

Assert::equal(array(
	'Adminer' => 2,
	'JUSH' => 1,
	'Nette' => 1,
	'Dibi' => 2,
), $tags);
