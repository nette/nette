<?php

/**
 * Test: Nette\Database\Statement: normalize row converts database types properly to PHP types
 *
 * @author     Jan Dolecek
 * @package    Nette\Database
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$res = $connection->query("SELECT * FROM bittest");
$row = $res->fetch();
Assert::same(0, $row->id);
Assert::same(false, $row->flag);

$row = $res->fetch();
Assert::same(1, $row->id);
Assert::same(true, $row->flag);
