<?php

/**
 * Test: Nette\Configurator and services inheritance and overwriting.
 *
 * @author     David Grudl
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addConfig('files/configurator.inheritance3.neon');


Assert::exception(function() use ($configurator) {
	$configurator->createContainer();
}, 'Nette\DI\ServiceCreationException', "Circular reference detected for service 'application'.");
