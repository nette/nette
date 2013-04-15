<?php

/**
 * Test: Nette\Database\Connection query methods.
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @dataProvider? databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/files/{$driverName}-nette_test1.sql");



$res = $connection->query('SELECT id FROM author WHERE id = ?', 11);
Assert::true($res instanceof Nette\Database\ResultSet);
Assert::same( 'SELECT id FROM author WHERE id = 11', $res->getQueryString() );


$res = $connection->query('SELECT id FROM author WHERE id = ? OR id = ?', 11, 12);
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $res->getQueryString() );


$res = $connection->queryArgs('SELECT id FROM author WHERE id = ? OR id = ?', array(11, 12));
Assert::same( 'SELECT id FROM author WHERE id = 11 OR id = 12', $res->getQueryString() );
