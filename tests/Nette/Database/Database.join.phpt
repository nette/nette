<?php

/**
 * Test: Nette\Database Join.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



foreach ($connection->table('application')->order('author.name, title') as $application) {
	echo $application->author->name . ": $application->title\n";
}

echo "\n";

foreach ($connection->table('application_tag')->where('application.author.name', 'Jakub Vrana')->group('application_tag.tag_id') as $application_tag) {
	echo $application_tag->tag->name . "\n";
}
