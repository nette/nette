<?php

/**
 * Test: Nette\Config\Configurator and loadConfig errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;

Assert::throws(function() use ($configurator) {
	$configurator->setCacheDirectory(NULL)->loadConfig('files/config1.neon');
}, 'Nette\InvalidStateException', "Set path to temporary directory using setCacheDirectory().");

$configurator->setCacheDirectory(TEMP_DIR);

$configurator->getContainer();

Assert::throws(function() use ($configurator) {
	$configurator->loadConfig('files/config1.neon');
}, 'Nette\InvalidStateException', "Container has already been created. Make sure you did not call getContainer() before loadConfig().");
