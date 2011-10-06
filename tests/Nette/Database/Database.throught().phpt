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
	foreach ($author->related('application')->through('maintainer_id') as $application) {
		$apps[$application->title] = $author->name;
	}
}

Assert::equal(array(
	'Adminer' => 'Jakub Vrana',
	'Nette' => 'David Grudl',
	'Dibi' => 'David Grudl',
), $apps);
