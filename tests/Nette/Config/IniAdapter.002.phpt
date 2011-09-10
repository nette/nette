<?php

/**
 * Test: Nette\Config\IniAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$config = Config::fromFile('config.missing-section.ini');
}, 'Nette\InvalidStateException', "Missing parent section [scalar] in file '%a%'.");


Assert::throws(function() {
	$config = Config::fromFile('config.scalar1.ini');
}, 'Nette\InvalidStateException', "Invalid section [scalar.set] in file '%a%'.");


Assert::throws(function() {
	$config = Config::fromFile('config.scalar2.ini');
}, 'Nette\InvalidStateException', "Invalid key 'date.timezone' in section [set] in file '%a%'.");
