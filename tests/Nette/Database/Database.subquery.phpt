<?php

/**
 * Test: Nette\Database Subqueries.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$unknownBorn = $connection->table('author')->where('born', null); // authors with unknown date of born
foreach ($connection->table('application')->where('author_id', $unknownBorn) as $application) { // their applications
	echo "$application[title]\n";
}
