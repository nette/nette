<?php

/**
 * Test: Nette\Database\Table: Fetch pairs.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");


test(function() use ($dao) {
	$apps = $dao->table('book')->order('title')->fetchPairs('id', 'title');  // SELECT * FROM `book` ORDER BY `title`
	Assert::same(array(
		1 => '1001 tipu a triku pro PHP',
		4 => 'Dibi',
		2 => 'JUSH',
		3 => 'Nette',
	), $apps);
});


test(function() use ($dao) {
	$ids = $dao->table('book')->order('id')->fetchPairs('id', 'id');  // SELECT * FROM `book` ORDER BY `id`
	Assert::same(array(
		1 => 1,
		2 => 2,
		3 => 3,
		4 => 4,
	), $ids);
});


test(function() use ($dao) {
	$dao->table('author')->get(11)->update(array('born' => new DateTime('2002-02-20')));
	$dao->table('author')->get(12)->update(array('born' => new DateTime('2002-02-02')));
	$list = $dao->table('author')->where('born IS NOT NULL')->order('born')->fetchPairs('born', 'name');
	Assert::same(array(
		'2002-02-02 00:00:00' => 'David Grudl',
		'2002-02-20 00:00:00' => 'Jakub Vrana',
	), $list);
});
