<?php

/**
 * Test: Nette\Database\Table: limit.
 *
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($dao) {
	$count = $dao->table('author')->limit(2)->count();
	Assert::equal(2, $count);
});



test(function() use ($dao) {
	$authors = $dao->table('author')->order('name')->limit(2);
	$names = array();
	foreach ($authors as $user) {
		$names[] = $user->name;
	}

	Assert::equal(array(
		'David Grudl',
		'Geek',
	), $names);
});
