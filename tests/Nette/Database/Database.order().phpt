<?php

/**
 * Test: Nette\Database Search and order items.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



foreach ($connection->table('application')->where('web LIKE ?', 'http://%')->order('title')->limit(3) as $application) {
	echo "$application[title]\n";
}
