<?php

/**
 * Test: Nette\Database Calling __toString().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



foreach ($connection->table('application') as $application) {
	echo "$application\n";
}
