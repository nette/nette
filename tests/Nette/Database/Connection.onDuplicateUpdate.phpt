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

$connection->query('INSERT INTO author ? ON DUPLICATE KEY UPDATE ?', array(
	'id' => 12,
	'name' => 'David Grudl',
	'web' => 'http://nette.org'
), array(
	'name' => 'Grudl David',
	'web' => 'http://nette.org',
));

Assert::equal("http://nette.org", $connection->table('author')->get(12)->web);
