<?php

/**
 * Test: Nette\Configurator and createContainer errors.
 *
 * @author     David Grudl
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;

Assert::exception(function() use ($configurator) {
	$configurator->addConfig('files/missing.neon')
		->createContainer();
}, 'Nette\InvalidStateException', "Set path to temporary directory using setTempDirectory().");
