<?php

/**
 * Test: Nette\Database\SqlPreprocessor
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @subpackage UnitTests
 * @multiple   databases.ini
 */

require __DIR__ . '/connect.inc.php'; // create $connection

Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/{$driverName}-nette_test1.sql");



$preprocessor = new Nette\Database\SqlPreprocessor($connection);

list($sql) = $preprocessor->process('INSERT INTO author', array(array(
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
	array('name' => 'Sansa Stark', 'born' => new DateTime('2021-11-11'))
)));

switch ($driverName) {
	case 'pgsql':
		Assert::same( "INSERT INTO author (\"name\", \"born\") VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')", $sql );
		break;
	case 'mysql':
	default:
		Assert::same( "INSERT INTO author (`name`, `born`) VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')", $sql );
		break;
}




list($sql) = $preprocessor->process('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?', array(
	array('id' => 12, 'name' => 'John Doe'),
	array('web' => 'http://nette.org', 'name' => 'Dave Lister'),
));

switch ($driverName) {
	case 'pgsql':
		Assert::same( "INSERT INTO author (\"id\", \"name\") VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE \"web\"='http://nette.org', \"name\"='Dave Lister'", $sql );
		break;
	case 'mysql':
	default:
		Assert::same( "INSERT INTO author (`id`, `name`) VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE `web`='http://nette.org', `name`='Dave Lister'", $sql );
		break;
}
