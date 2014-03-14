<?php

/**
 * Test: Nette\Database\ResultSet: Fetch assoc.
 *
 * @author     David Grudl
 * @dataProvider? databases.ini
 */

use Tester\Assert;

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($context) {
	$res = $context->query('SELECT * FROM book ORDER BY title');
	Assert::same(array(
		1 => '1001 tipu a triku pro PHP',
		4 => 'Dibi',
		2 => 'JUSH',
		3 => 'Nette',
	), $res->fetchAssoc('id=title'));
});


test(function() use ($context) {
	$pairs = $context->query('SELECT id FROM book ORDER BY id')->fetchAssoc('id');
	Assert::equal(array(
		1 => array('id' => 1),
		2 => array('id' => 2),
		3 => array('id' => 3),
		4 => array('id' => 4),
	), $pairs);
});


test(function() use ($context) {
	$pairs = $context->query('UPDATE author SET born = ? WHERE id = 11', new DateTime('2002-02-20'));
	$pairs = $context->query('UPDATE author SET born = ? WHERE id = 12', new DateTime('2002-02-02'));
	$pairs = $context->query('SELECT * FROM author WHERE born IS NOT NULL ORDER BY born')->fetchAssoc('born=name');
	Assert::same(array(
		'2002-02-02 00:00:00' => 'David Grudl',
		'2002-02-20 00:00:00' => 'Jakub Vrana',
	), $pairs);
});
