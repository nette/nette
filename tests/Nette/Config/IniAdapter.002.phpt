<?php

/**
 * Test: Nette\Config\Adapters\IniAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$config = new Config;
	$config->load('files/config.scalar1.ini');
}, 'Nette\InvalidStateException', "Invalid section [scalar.set] in file '%a%'.");


Assert::throws(function() {
	$config = new Config;
	$config->load('files/config.scalar2.ini');
}, 'Nette\InvalidStateException', "Invalid key 'date.timezone' in section [set] in file '%a%'.");
