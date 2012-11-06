<?php

/**
 * Test: Nette\Config\Adapters\IniAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/config.scalar1.ini');
}, 'Nette\InvalidStateException', "Invalid section [scalar.set] in file '%a%'.");


Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/config.scalar2.ini');
}, 'Nette\InvalidStateException', "Invalid key 'date.timezone' in section [set] in file '%a%'.");
