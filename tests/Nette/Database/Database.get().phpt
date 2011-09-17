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



$application = $connection->table('application')->get(1);
foreach ($application as $key => $val) {
	echo "$key: $val\n";
}
