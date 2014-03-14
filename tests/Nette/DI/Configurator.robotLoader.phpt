<?php

/**
 * Test: Nette\Configurator::createRobotLoader()
 *
 * @author     David Grudl
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;

Assert::exception(function() use ($configurator) {
	$configurator->createRobotLoader();
}, 'Nette\InvalidStateException', "Set path to temporary directory using setTempDirectory().");


$configurator->setTempDirectory(TEMP_DIR);
$loader = $configurator->createRobotLoader();

Assert::type( 'Nette\Loaders\RobotLoader', $loader );
Assert::type( 'Nette\Caching\Storages\FileStorage', $loader->getCacheStorage() );
