<?php

/**
 * Test: Nette\Database\Connection: Multi insert operations
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/nette_test1.sql');



$connection->query('INSERT INTO author', array(
	array(
		'name' => 'Catelyn Stark',
		'web' => 'http://example.com',
		'born' => new DateTime('2011-11-11'),
	),
	array(
		'name' => 'Sansa Stark',
		'web' => 'http://example.com',
		'born' => new DateTime('2021-11-11'),
	)
));  // INSERT INTO author (`name`, `web`, `born`) VALUES ('Catelyn Stark', 'http://example.com', '2011-11-11 00:00:00'), ('Sansa Stark', 'http://example.com', '2021-11-11 00:00:00')
