<?php

/**
 * Test: Nette\Config\Configurator and createContainer errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';


Assert::throws(function() {
	$configurator = new Configurator;
	$configurator->addConfig('files/config1.neon')
		->createContainer();
}, 'Nette\InvalidStateException', "Set path to temporary directory using setTempDirectory().");

Assert::throws(function () {
	$configurator = new Configurator;
	$configurator->addConfig('files/config.serviceIdentifiers.neon', $configurator::NONE)
		->setTempDirectory(TEMP_DIR)
		->createContainer();
}, 'Nette\DI\ServiceCreationException', "Service 'Nette\\Latte\\Token': Name contains invalid characters.");
