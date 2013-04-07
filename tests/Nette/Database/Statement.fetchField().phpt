<?php

/**
 * Test: Nette\Database\Statement: Fetch field.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");


$res = $connection->query('SELECT name, id FROM author ORDER BY id');

Assert::same('Jakub Vrana', $res->fetchField());
Assert::same(12, $res->fetchField(1));
Assert::same('Geek', $res->fetchField('name'));
