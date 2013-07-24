<?php

/**
 * Test: Nette\Database\Table: Basic operations with camelCase name conventions.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test2.sql");
$dao = new Nette\Database\SelectionFactory(
	$connection,
	new Nette\Database\Reflection\DiscoveredReflection($connection)
);


test(function() use ($dao) {
	$titles = array();
	foreach ($dao->table('nUsers')->order('nUserId') as $user) {
		foreach ($user->related('nUsers_nTopics')->order('nTopicId') as $userTopic) {
			$titles[$userTopic->nTopic->title] = $user->name;
		}
	}

	Assert::same(array(
		'Topic #1' => 'John',
		'Topic #3' => 'John',
		'Topic #2' => 'Doe',
	), $titles);
});
