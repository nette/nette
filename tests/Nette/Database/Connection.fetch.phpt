<?php

/**
 * Test: Nette\Database\Connection fetch methods.
 *
 * @author     David Grudl
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($context) { // fetch
	$row = $context->fetch('SELECT name, id FROM author WHERE id = ?', 11);
	Assert::type( 'Nette\Database\Row', $row );
	Assert::equal(Nette\Database\Row::from(array(
		'name' => 'Jakub Vrana',
		'id' => 11,
	)), $row);
});


test(function() use ($context) { // fetchField
	Assert::same('Jakub Vrana', $context->fetchField('SELECT name FROM author ORDER BY id'));
});


test(function() use ($context) { // fetchPairs
	$pairs = $context->fetchPairs('SELECT name, id FROM author WHERE id > ? ORDER BY id', 11);
	Assert::same(array(
		'David Grudl' => 12,
		'Geek' => 13,
	), $pairs);
});


test(function() use ($context) { // fetchAll
	$arr = $context->fetchAll('SELECT name, id FROM author WHERE id < ? ORDER BY id', 13);
	Assert::equal(array(
		Nette\Database\Row::from(array('name' => 'Jakub Vrana', 'id' => 11)),
		Nette\Database\Row::from(array('name' => 'David Grudl', 'id' => 12)),
	), $arr);
});
