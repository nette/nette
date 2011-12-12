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



$applicationExpected = array(
	'id' => 1,
	'title' => 'Adminer',
);

$application = iterator_to_array($connection->table('application')->where('id = ?', 1)->select('id, title')->fetch());
Assert::equal($applicationExpected, $application);

$application = iterator_to_array($connection->table('application')->select('id, title')->where('id = ?', 1)->fetch());
Assert::equal($applicationExpected, $application);



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
