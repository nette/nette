<?php

/**
 * Test: Nette\Database Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



foreach ($connection->table('application') as $application) {
	echo "$application->title (" . $application->author->name . ")\n";
	foreach ($application->related('application_tag') as $application_tag) {
		echo "\t" . $application_tag->tag->name . "\n";
	}
}
