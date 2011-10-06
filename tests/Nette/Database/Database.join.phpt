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



$apps = array();
foreach ($connection->table('application')->order('author.name, title') as $application) {
	$apps[$application->title] = $application->author->name;
}

Assert::equal(array(
	'Dibi' => 'David Grudl',
	'Nette' => 'David Grudl',
	'Adminer' => 'Jakub Vrana',
	'JUSH' => 'Jakub Vrana',
), $apps);



$tags = array();
foreach ($connection->table('application_tag')->where('application.author.name', 'Jakub Vrana')->group('application_tag.tag_id') as $application_tag) {
	$tags[] = $application_tag->tag->name;
}

Assert::equal(array(
	'PHP',
	'MySQL',
	'JavaScript',
), $tags);
