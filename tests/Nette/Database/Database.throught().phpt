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



foreach ($connection->table('author') as $author) {
	foreach ($author->related('application')->through('maintainer_id') as $application) {
		echo "$author->name: $application->title\n";
	}
}
