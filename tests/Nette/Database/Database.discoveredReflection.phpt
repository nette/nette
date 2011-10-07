<?php

/**
 * Test: Nette\Database Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

use Nette\Database;



require_once dirname(__FILE__) . '/connect.inc.php';
$reflection = new Database\Reflection\DiscoveredReflection;
$connection = new Database\Connection("sqlite:$dbFile", null, null, array(), $reflection);


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
