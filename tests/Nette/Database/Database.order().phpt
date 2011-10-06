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



$apps = array();
foreach ($connection->table('application')->where('web LIKE ?', 'http://%')->order('title')->limit(3) as $application) {
	$apps[] = $application->title;
}

Assert::equal(array(
	'Adminer',
	'Dibi',
	'JUSH',
), $apps);
