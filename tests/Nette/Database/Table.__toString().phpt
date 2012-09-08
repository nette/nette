<?php

/**
 * Test: Nette\Database\Table: Calling __toString().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



Assert::same('2', (string) $connection->table('book')->get(2));



Assert::same(2, $connection->table('book')->get(2)->getPrimary());



Assert::throws(function() use ($connection) {
	$appTag = $connection->table('book_tag')->where('book_id', 1)->where('tag_id', 21)->fetch();
	$appTag->getPrimary();
}, 'Nette\\NotSupportedException', 'Table book_tag does not have any primary key.');
