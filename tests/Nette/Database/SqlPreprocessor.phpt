<?php

/**
 * Test: Nette\Database\SqlPreprocessor
 *
 * @author     David Grudl
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require __DIR__ . '/connect.inc.php'; // create $connection


$preprocessor = new Nette\Database\SqlPreprocessor($connection);

list($sql) = $preprocessor->process('INSERT INTO author', array(array(
	array('name' => 'Catelyn Stark', 'born' => new DateTime('2011-11-11')),
	array('name' => 'Sansa Stark', 'born' => new DateTime('2021-11-11'))
)));

Assert::same( "INSERT INTO author (`name`, `born`) VALUES ('Catelyn Stark', '2011-11-11 00:00:00'), ('Sansa Stark', '2021-11-11 00:00:00')", $sql );



list($sql) = $preprocessor->process('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?', array(
	array('id' => 12, 'name' => 'John Doe'),
	array('web' => 'http://nette.org', 'name' => 'Dave Lister'),
));

Assert::same( "INSERT INTO author (`id`, `name`) VALUES (12, 'John Doe') ON DUPLICATE KEY UPDATE `web`='http://nette.org', `name`='Dave Lister'", $sql );
