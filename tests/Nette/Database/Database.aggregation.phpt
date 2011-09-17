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
echo "$count applications\n";

foreach ($connection->table('application') as $application) {
	$count = $application->related('application_tag')->count('*');
	echo "$application->title: $count tag(s)\n";
}
