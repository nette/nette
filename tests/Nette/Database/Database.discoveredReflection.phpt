<?php

/**
 * Test: Nette\Database Basic operations with DiscoveredReflection.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



require_once __DIR__ . '/connect.inc.php';
$connection->setDatabaseReflection(new Database\Reflection\DiscoveredReflection);



$appTags = array();
foreach ($connection->table('application') as $application) {
	$appTags[$application->title] = array(
		'author' => $application->author->name,
		'tags' => array(),
	);

	foreach ($application->related('application_tag') as $application_tag) {
		$appTags[$application->title]['tags'][] = $application_tag->tag->name;
	}
}

Assert::equal(array(
	'Adminer' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('PHP', 'MySQL'),
	),
	'JUSH' => array(
		'author' => 'Jakub Vrana',
		'tags' => array('JavaScript'),
	),
	'Nette' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP'),
	),
	'Dibi' => array(
		'author' => 'David Grudl',
		'tags' => array('PHP', 'MySQL'),
	),
), $appTags);




$application = $connection->table('application')->get(1);
Assert::equal('Jakub Vrana', $application->maintainer->name);
